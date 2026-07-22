<?php

namespace chgold\AIConnectPro\Module;

/**
 * Moderation tools for the Pro module (13 tools).
 *
 * Split into a trait to keep ProModule under the file-size ceiling. All tools
 * require the 'write' (or 'delete') scope and rely on XenForo's own permission
 * checks (canEdit/canDelete/canLockUnlock/...) so the acting user can never
 * exceed their real forum permissions.
 */
trait ProModerationTrait
{
    protected function registerModerationTools()
    {
        $intId = static function (string $desc): array {
            return ['type' => 'integer', 'description' => $desc];
        };

        $this->registerTool('editThread', [
            'description' => 'Edit a thread title and/or prefix',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => $intId('Thread to edit'),
                    'title' => ['type' => 'string', 'description' => 'New title (optional)'],
                    'prefix_id' => $intId('New prefix id (optional, 0 to clear)'),
                ],
            ],
        ]);
        $this->registerTool('lockThread', [
            'description' => 'Lock a thread (close it to new replies)',
            'input_schema' => ['type' => 'object', 'required' => ['thread_id'], 'properties' => ['thread_id' => $intId('Thread to lock')]],
        ]);
        $this->registerTool('unlockThread', [
            'description' => 'Unlock a thread (reopen it to replies)',
            'input_schema' => ['type' => 'object', 'required' => ['thread_id'], 'properties' => ['thread_id' => $intId('Thread to unlock')]],
        ]);
        $this->registerTool('stickThread', [
            'description' => 'Stick a thread to the top of its forum',
            'input_schema' => ['type' => 'object', 'required' => ['thread_id'], 'properties' => ['thread_id' => $intId('Thread to stick')]],
        ]);
        $this->registerTool('unstickThread', [
            'description' => 'Remove a thread from the stuck list',
            'input_schema' => ['type' => 'object', 'required' => ['thread_id'], 'properties' => ['thread_id' => $intId('Thread to unstick')]],
        ]);
        $this->registerTool('moveThread', [
            'description' => 'Move a thread to another forum',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id', 'target_forum_id'],
                'properties' => [
                    'thread_id' => $intId('Thread to move'),
                    'target_forum_id' => $intId('Destination forum id'),
                    'redirect' => ['type' => 'boolean', 'description' => 'Leave a redirect in the old forum (default false)'],
                ],
            ],
        ]);
        $this->registerTool('deleteThread', [
            'description' => 'Delete a thread (soft by default)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => $intId('Thread to delete'),
                    'hard_delete' => ['type' => 'boolean', 'description' => 'Permanently delete (default false)'],
                    'reason' => ['type' => 'string', 'description' => 'Deletion reason (optional)'],
                ],
            ],
        ]);
        $this->registerTool('deletePost', [
            'description' => 'Delete a post (soft by default)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id'],
                'properties' => [
                    'post_id' => $intId('Post to delete'),
                    'hard_delete' => ['type' => 'boolean', 'description' => 'Permanently delete (default false)'],
                    'reason' => ['type' => 'string', 'description' => 'Deletion reason (optional)'],
                ],
            ],
        ]);
        $this->registerTool('featureThread', [
            'description' => 'Add a thread to featured content',
            'input_schema' => ['type' => 'object', 'required' => ['thread_id'], 'properties' => ['thread_id' => $intId('Thread to feature')]],
        ]);
        $this->registerTool('unfeatureThread', [
            'description' => 'Remove a thread from featured content',
            'input_schema' => ['type' => 'object', 'required' => ['thread_id'], 'properties' => ['thread_id' => $intId('Thread to unfeature')]],
        ]);
        $this->registerTool('markSolution', [
            'description' => 'Mark a post as the solution of its question thread (toggle)',
            'input_schema' => ['type' => 'object', 'required' => ['post_id'], 'properties' => ['post_id' => $intId('Post to mark as solution')]],
        ]);
        $this->registerTool('changeThreadType', [
            'description' => 'Change a thread discussion type (discussion/question/...)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id', 'discussion_type'],
                'properties' => [
                    'thread_id' => $intId('Thread to change'),
                    'discussion_type' => ['type' => 'string', 'description' => 'Target type id, e.g. discussion or question'],
                ],
            ],
        ]);
        $this->registerTool('deleteProfilePost', [
            'description' => 'Delete a profile post (soft by default)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['profile_post_id'],
                'properties' => [
                    'profile_post_id' => $intId('Profile post to delete'),
                    'hard_delete' => ['type' => 'boolean', 'description' => 'Permanently delete (default false)'],
                ],
            ],
        ]);
        $this->registerTool('deleteProfilePostComment', [
            'description' => 'Delete a comment on a profile post (soft by default)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['comment_id'],
                'properties' => [
                    'comment_id' => $intId('Profile post comment to delete'),
                    'hard_delete' => ['type' => 'boolean', 'description' => 'Permanently delete (default false)'],
                ],
            ],
        ]);
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- dynamic dispatch execute_<name>

    public function execute_editThread($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        if (!$thread->canEdit()) {
            return $this->error('no_permission', 'You cannot edit this thread');
        }
        $editor = \XF::service('XF:Thread\Editor', $thread);
        if (isset($params['title']) && trim($params['title']) !== '') {
            $editor->setTitle($params['title']);
        }
        if (isset($params['prefix_id'])) {
            $editor->setPrefix((int) $params['prefix_id']);
        }
        if (!$editor->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $editor->save();
        return $this->success(['thread_id' => $thread->thread_id, 'title' => $thread->title, 'prefix_id' => $thread->prefix_id]);
    }

    public function execute_lockThread($params)
    {
        return $this->setThreadOpen($params['thread_id'], false);
    }

    public function execute_unlockThread($params)
    {
        return $this->setThreadOpen($params['thread_id'], true);
    }

    public function execute_stickThread($params)
    {
        return $this->setThreadSticky($params['thread_id'], true);
    }

    public function execute_unstickThread($params)
    {
        return $this->setThreadSticky($params['thread_id'], false);
    }

    public function execute_moveThread($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        $target = \XF::em()->find('XF:Forum', $params['target_forum_id']);
        if (!$target) {
            return $this->error('not_found', 'Target forum not found');
        }
        if (!$thread->canMove()) {
            return $this->error('no_permission', 'You cannot move this thread');
        }
        $mover = \XF::service('XF:Thread\Mover', $thread);
        if (!empty($params['redirect'])) {
            $mover->setRedirect(true);
        }
        $mover->move($target);
        return $this->success(['thread_id' => $thread->thread_id, 'node_id' => $target->node_id]);
    }

    public function execute_deleteThread($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        $hard = !empty($params['hard_delete']);
        if (!$thread->canDelete($hard ? 'hard' : 'soft')) {
            return $this->error('no_permission', 'You cannot delete this thread');
        }
        $deleter = \XF::service('XF:Thread\Deleter', $thread);
        $deleter->delete($hard ? 'hard' : 'soft', $params['reason'] ?? '');
        return $this->success(['thread_id' => $params['thread_id'], 'hard_delete' => $hard]);
    }

    public function execute_deletePost($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $post = \XF::em()->find('XF:Post', $params['post_id']);
        if (!$post) {
            return $this->error('not_found', 'Post not found');
        }
        $hard = !empty($params['hard_delete']);
        if (!$post->canDelete($hard ? 'hard' : 'soft')) {
            return $this->error('no_permission', 'You cannot delete this post');
        }
        $deleter = \XF::service('XF:Post\Deleter', $post);
        $deleter->delete($hard ? 'hard' : 'soft', $params['reason'] ?? '');
        return $this->success(['post_id' => $params['post_id'], 'hard_delete' => $hard]);
    }

    public function execute_featureThread($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        if (!$thread->canFeatureUnfeature()) {
            return $this->error('no_permission', 'You cannot feature this thread');
        }
        if ($thread->Feature) {
            return $this->success(['thread_id' => $thread->thread_id, 'featured' => true]);
        }
        $creator = \XF::service('XF:FeaturedContent\Creator', $thread);
        if (!$creator->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $creator->save();
        return $this->success(['thread_id' => $thread->thread_id, 'featured' => true]);
    }

    public function execute_unfeatureThread($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        if (!$thread->canFeatureUnfeature()) {
            return $this->error('no_permission', 'You cannot unfeature this thread');
        }
        if (!$thread->Feature) {
            return $this->success(['thread_id' => $thread->thread_id, 'featured' => false]);
        }
        $deleter = \XF::service('XF:FeaturedContent\Deleter', $thread->Feature);
        $deleter->delete();
        return $this->success(['thread_id' => $thread->thread_id, 'featured' => false]);
    }

    public function execute_markSolution($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $post = \XF::em()->find('XF:Post', $params['post_id']);
        if (!$post) {
            return $this->error('not_found', 'Post not found');
        }
        $thread = $post->Thread;
        if (!$thread || !$thread->canMarkSolution()) {
            return $this->error('no_permission', 'You cannot mark a solution on this thread');
        }
        $marker = \XF::service('XF:ThreadQuestion\MarkSolution', $thread);
        $current = $thread->Question ? $thread->Question->solution_post_id : 0;
        if ($current == $post->post_id) {
            $marker->unmarkSolution();
            return $this->success(['thread_id' => $thread->thread_id, 'solution_post_id' => 0]);
        }
        $marker->markSolution($post);
        return $this->success(['thread_id' => $thread->thread_id, 'solution_post_id' => $post->post_id]);
    }

    public function execute_changeThreadType($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        if (!$thread->canChangeType()) {
            return $this->error('no_permission', 'You cannot change the type of this thread');
        }
        $service = \XF::service('XF:Thread\ChangeType', $thread);
        $service->setDiscussionTypeDataRaw(['discussion_type' => $params['discussion_type']]);
        if (!$service->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $service->save();
        return $this->success(['thread_id' => $thread->thread_id, 'discussion_type' => $thread->discussion_type]);
    }

    public function execute_deleteProfilePost($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $pp = \XF::em()->find('XF:ProfilePost', $params['profile_post_id']);
        if (!$pp) {
            return $this->error('not_found', 'Profile post not found');
        }
        $hard = !empty($params['hard_delete']);
        if (!$pp->canDelete($hard ? 'hard' : 'soft')) {
            return $this->error('no_permission', 'You cannot delete this profile post');
        }
        $deleter = \XF::service('XF:ProfilePost\Deleter', $pp);
        $deleter->delete($hard ? 'hard' : 'soft');
        return $this->success(['profile_post_id' => $params['profile_post_id'], 'hard_delete' => $hard]);
    }

    public function execute_deleteProfilePostComment($params)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $comment = \XF::em()->find('XF:ProfilePostComment', $params['comment_id']);
        if (!$comment) {
            return $this->error('not_found', 'Profile post comment not found');
        }
        $hard = !empty($params['hard_delete']);
        if (!$comment->canDelete($hard ? 'hard' : 'soft')) {
            return $this->error('no_permission', 'You cannot delete this comment');
        }
        $deleter = \XF::service('XF:ProfilePostComment\Deleter', $comment);
        $deleter->delete($hard ? 'hard' : 'soft');
        return $this->success(['comment_id' => $params['comment_id'], 'hard_delete' => $hard]);
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    private function setThreadOpen($threadId, bool $open)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $thread = \XF::em()->find('XF:Thread', $threadId);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        if (!$thread->canLockUnlock()) {
            return $this->error('no_permission', 'You cannot lock/unlock this thread');
        }
        $thread->discussion_open = $open;
        $thread->save();
        return $this->success(['thread_id' => $thread->thread_id, 'discussion_open' => $open]);
    }

    private function setThreadSticky($threadId, bool $sticky)
    {
        if ($err = $this->requireWrite()) {
            return $err;
        }
        $thread = \XF::em()->find('XF:Thread', $threadId);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }
        if (!$thread->canStickUnstick()) {
            return $this->error('no_permission', 'You cannot stick/unstick this thread');
        }
        $thread->sticky = $sticky;
        $thread->save();
        return $this->success(['thread_id' => $thread->thread_id, 'sticky' => $sticky]);
    }
}
