<?php

namespace chgold\AIConnectPro\Module;

use XF\Finder\ConversationUserFinder;

/**
 * Conversation-state and alert engagement tools for the Pro module (9 tools):
 * read messages, mark read, star, leave, invite; list/get/mark alerts.
 * Split into a trait to keep ProModule under the file-size ceiling.
 */
trait ProConversationTrait
{
    protected function registerConversationTools()
    {
        $convId = ['type' => 'integer', 'description' => 'Conversation id'];

        $this->registerTool('getConversationMessages', [
            'description' => 'Get the messages in a private conversation',
            'input_schema' => [
                'type' => 'object',
                'required' => ['conversation_id'],
                'properties' => [
                    'conversation_id' => $convId,
                    'limit' => ['type' => 'integer', 'description' => 'Max messages (default 20)'],
                ],
            ],
        ]);
        $this->registerTool('markConversationRead', [
            'description' => 'Mark a conversation as read',
            'input_schema' => ['type' => 'object', 'required' => ['conversation_id'], 'properties' => ['conversation_id' => $convId]],
        ]);
        $this->registerTool('starConversation', [
            'description' => 'Star or unstar a conversation (toggle)',
            'input_schema' => ['type' => 'object', 'required' => ['conversation_id'], 'properties' => ['conversation_id' => $convId]],
        ]);
        $this->registerTool('leaveConversation', [
            'description' => 'Leave a conversation',
            'input_schema' => ['type' => 'object', 'required' => ['conversation_id'], 'properties' => ['conversation_id' => $convId]],
        ]);
        $this->registerTool('inviteToConversation', [
            'description' => 'Invite users to a conversation you can invite to',
            'input_schema' => [
                'type' => 'object',
                'required' => ['conversation_id', 'usernames'],
                'properties' => [
                    'conversation_id' => $convId,
                    'usernames' => ['type' => 'array', 'description' => 'Usernames to invite'],
                ],
            ],
        ]);
        $this->registerTool('listAlerts', [
            'description' => 'List your alerts',
            'input_schema' => [
                'type' => 'object',
                'properties' => ['limit' => ['type' => 'integer', 'description' => 'Max alerts (default 20)']],
            ],
        ]);
        $this->registerTool('getAlert', [
            'description' => 'Get a single alert by id',
            'input_schema' => ['type' => 'object', 'required' => ['alert_id'], 'properties' => ['alert_id' => ['type' => 'integer', 'description' => 'Alert id']]],
        ]);
        $this->registerTool('markAlertRead', [
            'description' => 'Mark one alert as read',
            'input_schema' => ['type' => 'object', 'required' => ['alert_id'], 'properties' => ['alert_id' => ['type' => 'integer', 'description' => 'Alert id']]],
        ]);
        $this->registerTool('markAllAlertsRead', [
            'description' => 'Mark all your alerts as read',
            'input_schema' => ['type' => 'object', 'properties' => new \stdClass()],
        ]);
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- dynamic dispatch execute_<name>

    public function execute_getConversationMessages($params)
    {
        $conv = \XF::em()->find('XF:ConversationMaster', $params['conversation_id']);
        if (!$conv || !$conv->canView()) {
            return $this->error('not_found', 'Conversation not found');
        }
        $limit = max(1, min(50, (int) ($params['limit'] ?? 20)));
        $finder = \XF::finder('XF:ConversationMessage')
            ->where('conversation_id', $conv->conversation_id)
            ->order('message_date', 'desc')
            ->limit($limit);
        $out = [];
        foreach ($finder->fetch() as $msg) {
            $out[] = [
                'message_id' => $msg->message_id,
                'user_id' => $msg->user_id,
                'username' => $msg->username,
                'message' => $msg->message,
                'message_date' => $msg->message_date,
            ];
        }
        return $this->success(array_reverse($out));
    }

    public function execute_markConversationRead($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $userConv = $this->getVisitorConversation($params['conversation_id']);
        if (!$userConv) {
            return $this->error('not_found', 'Conversation not found');
        }
        if ($userConv->is_unread) {
            $userConv->is_unread = false;
            $userConv->save();
        }
        return $this->success(['conversation_id' => (int) $params['conversation_id'], 'is_unread' => false]);
    }

    public function execute_starConversation($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $userConv = $this->getVisitorConversation($params['conversation_id']);
        if (!$userConv) {
            return $this->error('not_found', 'Conversation not found');
        }
        $userConv->is_starred = !$userConv->is_starred;
        $userConv->save();
        return $this->success(['conversation_id' => (int) $params['conversation_id'], 'is_starred' => $userConv->is_starred]);
    }

    public function execute_leaveConversation($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $userConv = $this->getVisitorConversation($params['conversation_id']);
        if (!$userConv) {
            return $this->error('not_found', 'Conversation not found');
        }
        $recipient = $userConv->Recipient;
        if ($recipient) {
            $recipient->recipient_state = 'deleted';
            $recipient->save();
        }
        return $this->success(['conversation_id' => (int) $params['conversation_id'], 'left' => true]);
    }

    public function execute_inviteToConversation($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        if (!is_array($params['usernames'])) {
            return $this->error('invalid_param', 'usernames must be an array');
        }
        $conv = \XF::em()->find('XF:ConversationMaster', $params['conversation_id']);
        if (!$conv) {
            return $this->error('not_found', 'Conversation not found');
        }
        if (!$conv->canInvite()) {
            return $this->error('no_permission', 'You cannot invite users to this conversation');
        }
        $inviter = \XF::service('XF:Conversation\Inviter', $conv, \XF::visitor());
        $inviter->setRecipients($params['usernames']);
        if (!$inviter->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $inviter->save();
        return $this->success(['conversation_id' => $conv->conversation_id, 'invited' => array_values($params['usernames'])]);
    }

    public function execute_listAlerts($params)
    {
        $visitor = \XF::visitor();
        $limit = max(1, min(50, (int) ($params['limit'] ?? 20)));
        $finder = \XF::repository('XF:UserAlert')->findAlertsForUser($visitor->user_id);
        $finder->limit($limit);
        $out = [];
        foreach ($finder->fetch() as $alert) {
            if (!$alert->canView()) {
                continue;
            }
            $out[] = $this->alertData($alert);
        }
        return $this->success($out);
    }

    public function execute_getAlert($params)
    {
        $alert = \XF::em()->find('XF:UserAlert', $params['alert_id']);
        if (!$alert || $alert->alerted_user_id != \XF::visitor()->user_id) {
            return $this->error('not_found', 'Alert not found');
        }
        return $this->success($this->alertData($alert));
    }

    public function execute_markAlertRead($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $alert = \XF::em()->find('XF:UserAlert', $params['alert_id']);
        if (!$alert || $alert->alerted_user_id != \XF::visitor()->user_id) {
            return $this->error('not_found', 'Alert not found');
        }
        \XF::repository('XF:UserAlert')->markUserAlertRead($alert);
        return $this->success(['alert_id' => (int) $params['alert_id'], 'read' => true]);
    }

    public function execute_markAllAlertsRead($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        \XF::repository('XF:UserAlert')->markUserAlertsRead(\XF::visitor());
        return $this->success(['read' => 'all']);
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    private function getVisitorConversation($conversationId)
    {
        $visitor = \XF::visitor();
        /** @var ConversationUserFinder $finder */
        $finder = \XF::finder(ConversationUserFinder::class);
        $finder->forUser($visitor, false);
        $finder->where('conversation_id', $conversationId);
        return $finder->fetchOne();
    }

    private function alertData($alert): array
    {
        return [
            'alert_id' => $alert->alert_id,
            'content_type' => $alert->content_type,
            'content_id' => $alert->content_id,
            'action' => $alert->action,
            'unread' => !$alert->view_date,
            'event_date' => $alert->event_date,
        ];
    }
}
