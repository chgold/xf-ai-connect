<?php

namespace chgold\AIConnectPro\Module;

/**
 * Writing tools for the Pro module (5 tools): thread tag editing and
 * conversation reply/edit/update. Split into a trait to keep ProModule under
 * the file-size ceiling. All rely on XenForo's own permission checks.
 */
trait ProWritingTrait
{
    protected function registerWritingTools()
    {
        $this->registerTool('addThreadTags', [
            'description' => 'Add tags to a thread',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id', 'tags'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread to tag'],
                    'tags' => ['type' => 'array', 'description' => 'Tags to add (array of strings)'],
                ],
            ],
        ]);
        $this->registerTool('removeThreadTags', [
            'description' => 'Remove tags from a thread',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id', 'tags'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread to untag'],
                    'tags' => ['type' => 'array', 'description' => 'Tags to remove (array of strings)'],
                ],
            ],
        ]);
        $this->registerTool('replyToConversation', [
            'description' => 'Post a reply to an existing private conversation',
            'input_schema' => [
                'type' => 'object',
                'required' => ['conversation_id', 'message'],
                'properties' => [
                    'conversation_id' => ['type' => 'integer', 'description' => 'Conversation to reply to'],
                    'message' => ['type' => 'string', 'description' => 'Reply content'],
                ],
            ],
        ]);
        $this->registerTool('editConversationMsg', [
            'description' => 'Edit one of your conversation messages',
            'input_schema' => [
                'type' => 'object',
                'required' => ['message_id', 'message'],
                'properties' => [
                    'message_id' => ['type' => 'integer', 'description' => 'Conversation message id'],
                    'message' => ['type' => 'string', 'description' => 'New content'],
                ],
            ],
        ]);
        $this->registerTool('updateConversation', [
            'description' => 'Update a conversation you started (title / open state / open invite)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['conversation_id'],
                'properties' => [
                    'conversation_id' => ['type' => 'integer', 'description' => 'Conversation to update'],
                    'title' => ['type' => 'string', 'description' => 'New title (optional)'],
                    'open_invite' => ['type' => 'boolean', 'description' => 'Allow recipients to invite others (optional)'],
                    'conversation_open' => ['type' => 'boolean', 'description' => 'Whether the conversation is open for replies (optional)'],
                ],
            ],
        ]);
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- dynamic dispatch execute_<name>

    public function execute_addThreadTags($params)
    {
        return $this->changeThreadTags($params['thread_id'], $params['tags'], true);
    }

    public function execute_removeThreadTags($params)
    {
        return $this->changeThreadTags($params['thread_id'], $params['tags'], false);
    }

    public function execute_replyToConversation($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $conv = \XF::em()->find('XF:ConversationMaster', $params['conversation_id']);
        if (!$conv) {
            return $this->error('not_found', 'Conversation not found');
        }
        if (!$conv->canReply()) {
            return $this->error('no_permission', 'You cannot reply to this conversation');
        }
        $replier = \XF::service('XF:Conversation\Replier', $conv, \XF::visitor());
        $replier->setMessageContent($params['message']);
        if (!$replier->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $message = $replier->save();
        return $this->success(['conversation_id' => $conv->conversation_id, 'message_id' => $message->message_id]);
    }

    public function execute_editConversationMsg($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $message = \XF::em()->find('XF:ConversationMessage', $params['message_id']);
        if (!$message) {
            return $this->error('not_found', 'Conversation message not found');
        }
        if (!$message->canEdit()) {
            return $this->error('no_permission', 'You cannot edit this message');
        }
        $editor = \XF::service('XF:Conversation\MessageEditor', $message);
        $editor->setMessageContent($params['message']);
        if (!$editor->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $editor->save();
        return $this->success(['message_id' => $message->message_id]);
    }

    public function execute_updateConversation($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $conv = \XF::em()->find('XF:ConversationMaster', $params['conversation_id']);
        if (!$conv) {
            return $this->error('not_found', 'Conversation not found');
        }
        if (!$conv->canEdit()) {
            return $this->error('no_permission', 'You cannot edit this conversation');
        }
        $editor = \XF::service('XF:Conversation\Editor', $conv);
        if (isset($params['title']) && trim($params['title']) !== '') {
            $editor->setTitle($params['title']);
        }
        if (isset($params['open_invite'])) {
            $editor->setOpenInvite((bool) $params['open_invite']);
        }
        if (isset($params['conversation_open'])) {
            $editor->setConversationOpen((bool) $params['conversation_open']);
        }
        if (!$editor->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $editor->save();
        return $this->success([
            'conversation_id' => $conv->conversation_id,
            'title' => $conv->title,
            'open_invite' => $conv->open_invite,
            'conversation_open' => $conv->conversation_open,
        ]);
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    private function changeThreadTags($threadId, $tags, bool $add)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        if (!is_array($tags)) {
            return $this->error('invalid_param', 'tags must be an array of strings');
        }
        $thread = \XF::em()->find('XF:Thread', $threadId);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        if (!$thread->canEditTags()) {
            return $this->error('no_permission', 'You cannot edit tags on this thread');
        }
        $changer = \XF::service('XF:Tag\Changer', 'thread', $thread);
        if (!$changer->canEdit()) {
            return $this->error('no_permission', 'You cannot edit tags on this thread');
        }
        if ($add) {
            $changer->addTags($tags);
        } else {
            $changer->removeTags($tags);
        }
        if (!$changer->save()) {
            return $this->error('validation_failed', 'Could not save tag changes');
        }
        $thread = \XF::em()->find('XF:Thread', $threadId);
        $current = [];
        if ($thread->tags) {
            foreach ($thread->tags as $tag) {
                $current[] = $tag['tag'];
            }
        }
        return $this->success(['thread_id' => $threadId, 'tags' => $current]);
    }
}
