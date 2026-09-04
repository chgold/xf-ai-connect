<?php

namespace chgold\AIConnect\Module;

/**
 * Conversation (direct message) module — exposes the visitor's private
 * conversations, their messages, and lets them start / reply to conversations.
 */
class ConversationModule extends ModuleBase
{
    protected $moduleName = 'conversation';

    protected function registerTools()
    {
        $this->registerTool('getConversations', [
            'description' => "List the current user's private conversations (direct messages).",
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'limit' => ['type' => 'integer', 'description' => 'Max conversations (default 20)'],
                    'unread_only' => ['type' => 'boolean', 'description' => 'Only conversations with unread messages'],
                ],
            ],
            'required_scope' => 'read',
        ]);

        $this->registerTool('getConversation', [
            'description' => 'Get one conversation (title, participants, message count).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['conversation_id'],
                'properties' => ['conversation_id' => ['type' => 'integer']],
            ],
            'required_scope' => 'read',
        ]);

        $this->registerTool('getConversationMessages', [
            'description' => 'List the messages in a conversation.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['conversation_id'],
                'properties' => [
                    'conversation_id' => ['type' => 'integer'],
                    'limit' => ['type' => 'integer', 'description' => 'Max messages (default 50)'],
                ],
            ],
            'required_scope' => 'read',
        ]);

        $this->registerTool('startConversation', [
            'description' => 'Start a new private conversation with one or more recipients.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['recipients', 'title', 'message'],
                'properties' => [
                    'recipients' => ['type' => 'array', 'description' => 'Recipient usernames'],
                    'title' => ['type' => 'string'],
                    'message' => ['type' => 'string'],
                ],
            ],
            'required_scope' => 'write',
        ]);

        $this->registerTool('replyToConversation', [
            'description' => 'Post a reply to an existing conversation.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['conversation_id', 'message'],
                'properties' => [
                    'conversation_id' => ['type' => 'integer'],
                    'message' => ['type' => 'string'],
                ],
            ],
            'required_scope' => 'write',
        ]);
    }

    protected function formatConversation($conv, $convUser = null)
    {
        return [
            'conversation_id' => (int) $conv->conversation_id,
            'title' => (string) $conv->title,
            'start_date' => (int) $conv->start_date,
            'last_message_date' => (int) $conv->last_message_date,
            'reply_count' => (int) $conv->reply_count,
            'recipient_count' => (int) $conv->recipient_count,
            'is_unread' => $convUser ? (bool) $convUser->is_unread : null,
        ];
    }

    protected function loadConversationForVisitor($conversationId, &$error)
    {
        $error = null;
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            $error = $this->error('not_authenticated', 'You must be logged in to access conversations');
            return null;
        }
        $convUser = \XF::em()->find('XF:ConversationUser', [
            'conversation_id' => $conversationId,
            'owner_user_id' => $visitor->user_id,
        ], ['Master']);
        if (!$convUser || !$convUser->Master) {
            $error = $this->error('not_found', 'Conversation not found or not accessible');
            return null;
        }
        return $convUser;
    }

    public function execute_getConversations($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'You must be logged in to access conversations');
        }
        $limit = isset($params['limit']) ? max(1, (int) $params['limit']) : 20;
        $finder = \XF::finder('XF:ConversationUser')
            ->where('owner_user_id', $visitor->user_id)
            ->with('Master')
            ->order('last_message_date', 'DESC')
            ->limit($limit);
        if (!empty($params['unread_only'])) {
            $finder->where('is_unread', 1);
        }
        $out = [];
        foreach ($finder->fetch() as $convUser) {
            if ($convUser->Master) {
                $out[] = $this->formatConversation($convUser->Master, $convUser);
            }
        }
        return $this->success(['conversations' => $out, 'count' => count($out)]);
    }

    public function execute_getConversation($params)
    {
        $convUser = $this->loadConversationForVisitor((int) $params['conversation_id'], $error);
        if (!$convUser) {
            return $error;
        }
        $data = $this->formatConversation($convUser->Master, $convUser);
        $recipients = [];
        foreach ($convUser->Master->recipients as $userId => $recipient) {
            $recipients[] = ['user_id' => (int) $userId, 'username' => (string) ($recipient->Recipient->username ?? '')];
        }
        $data['recipients'] = $recipients;
        return $this->success($data);
    }

    public function execute_getConversationMessages($params)
    {
        $convUser = $this->loadConversationForVisitor((int) $params['conversation_id'], $error);
        if (!$convUser) {
            return $error;
        }
        $limit = isset($params['limit']) ? max(1, (int) $params['limit']) : 50;
        $messages = \XF::finder('XF:ConversationMessage')
            ->where('conversation_id', (int) $params['conversation_id'])
            ->with('User')
            ->order('message_date', 'ASC')
            ->limit($limit)
            ->fetch();
        $out = [];
        foreach ($messages as $msg) {
            $out[] = [
                'message_id' => (int) $msg->message_id,
                'user_id' => (int) $msg->user_id,
                'username' => (string) $msg->username,
                'message_date' => (int) $msg->message_date,
                'message' => (string) $msg->message,
            ];
        }
        return $this->success([
            'conversation_id' => (int) $params['conversation_id'],
            'messages' => $out,
            'count' => count($out),
        ]);
    }

    public function execute_startConversation($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'You must be logged in to start a conversation');
        }
        $recipients = [];
        foreach ((array) $params['recipients'] as $username) {
            $user = \XF::finder('XF:User')->where('username', $username)->fetchOne();
            if ($user) {
                $recipients[$user->user_id] = $user;
            }
        }
        if (!$recipients) {
            return $this->error('no_recipients', 'None of the given usernames resolved to a user');
        }
        /** @var \XF\Service\Conversation\Creator $creator */
        $creator = \XF::service('XF:Conversation\Creator', $visitor);
        $creator->setRecipientsTrusted($recipients);
        $creator->setContent((string) $params['title'], (string) $params['message']);
        if (!$creator->validate($errors)) {
            return $this->error('validation_error', implode('; ', $errors));
        }
        $conversation = $creator->save();
        return $this->success([
            'conversation_id' => (int) $conversation->conversation_id,
            'title' => (string) $conversation->title,
            'recipients' => count($recipients),
        ]);
    }

    public function execute_replyToConversation($params)
    {
        $convUser = $this->loadConversationForVisitor((int) $params['conversation_id'], $error);
        if (!$convUser) {
            return $error;
        }
        $conversation = $convUser->Master;
        /** @var \XF\Service\Conversation\Replier $replier */
        $replier = \XF::service('XF:Conversation\Replier', $conversation, \XF::visitor());
        $replier->setMessageContent((string) $params['message']);
        if (!$replier->validate($errors)) {
            return $this->error('validation_error', implode('; ', $errors));
        }
        $message = $replier->save();
        return $this->success([
            'conversation_id' => (int) $conversation->conversation_id,
            'message_id' => (int) $message->message_id,
        ]);
    }
}
