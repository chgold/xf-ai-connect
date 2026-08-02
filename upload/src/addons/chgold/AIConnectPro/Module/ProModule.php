<?php

namespace chgold\AIConnectPro\Module;

use chgold\AIConnect\Module\ModuleBase;

class ProModule extends ModuleBase
{
    use ProCoreTrait;
    use ProModerationTrait;
    use ProWritingTrait;
    use ProEngagementTrait;
    use ProProfileTrait;
    use ProConversationTrait;
    use ProMediaTrait;
    use ProAutomationTrait;

    protected $moduleName = 'xenforo_pro';

    /** All Pro tools require the 'pro' package permission (use_package_pro). */
    protected $packageId = 'pro';

    /**
     * Shared guard: every write/delete Pro tool must hold the 'write' scope.
     * Returns an error array to short-circuit on, or null when allowed.
     */
    protected function requireWrite()
    {
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('write')) {
            return $this->error('insufficient_scope', 'The "write" scope is required for this operation');
        }
        return null;
    }

    /**
     * Shared guard for administrative tools: requires BOTH the 'admin' token
     * scope AND the acting user actually being a XenForo admin. Returns an error
     * array to short-circuit on, or null when allowed.
     */
    protected function requireAdmin()
    {
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('admin')) {
            return $this->error('insufficient_scope', 'The "admin" scope is required for this operation');
        }
        if (!\XF::visitor()->is_admin) {
            return $this->error('no_permission', 'This operation requires an administrator account');
        }
        return null;
    }

    protected function registerTools()
    {
        // Bundle-per-trait gating (BUNDLES-LICENSE-SPEC.md v1.0).
        // The license grants a list of bundle keys; each trait registers its
        // tools only when the current license includes its bundle. The '*'
        // wildcard (default backward-compat verdict) unlocks everything.
        $bundles = \chgold\AIConnectPro\License\Validator::getBundles();
        $has = static function (string $bundle) use ($bundles): bool {
            return in_array('*', $bundles, true) || in_array($bundle, $bundles, true);
        };

        // 'core' — identity/lookup helpers every other bundle depends on.
        // Loaded whenever ANY paid bundle is active so a customer who buys
        // "moderation only" still gets getMe/findUserByName (otherwise
        // moderation calls that reference a user id would be dead in the water).
        if (!empty($bundles)) {
            $this->registerCoreTools();
        }

        if ($has('moderation')) {
            $this->registerModerationTools();
        }
        if ($has('writing')) {
            $this->registerWritingTools();
        }
        if ($has('engagement')) {
            $this->registerEngagementTools();
        }
        if ($has('profile')) {
            $this->registerProfileTools();
        }
        if ($has('conversation')) {
            $this->registerConversationTools();
        }
        if ($has('media')) {
            $this->registerMediaTools();
        }
        if ($has('automation')) {
            $this->registerAutomationTools();
        }

        $this->registerTool('getForumList', [
            'description' => 'Get list of all accessible forums/nodes',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
            ],
        ]);

        $this->registerTool('createThread', [
            'description' => 'Create a new thread in a forum',
            'input_schema' => [
                'type' => 'object',
                'required' => ['forum_id', 'title', 'message'],
                'properties' => [
                    'forum_id' => [
                        'type' => 'integer',
                        'description' => 'ID of the forum to post in',
                    ],
                    'title' => [
                        'type' => 'string',
                        'description' => 'Thread title',
                    ],
                    'message' => [
                        'type' => 'string',
                        'description' => 'Thread body (first post content)',
                    ],
                ],
            ],
        ]);

        $this->registerTool('replyToThread', [
            'description' => 'Post a reply to an existing thread',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id', 'message'],
                'properties' => [
                    'thread_id' => [
                        'type' => 'integer',
                        'description' => 'ID of the thread to reply to',
                    ],
                    'message' => [
                        'type' => 'string',
                        'description' => 'Reply content',
                    ],
                ],
            ],
        ]);

        $this->registerTool('editPost', [
            'description' => 'Edit an existing post',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id', 'message'],
                'properties' => [
                    'post_id' => [
                        'type' => 'integer',
                        'description' => 'ID of the post to edit',
                    ],
                    'message' => [
                        'type' => 'string',
                        'description' => 'New post content',
                    ],
                ],
            ],
        ]);

        $this->registerTool('sendConversation', [
            'description' => 'Send a private conversation (PM) to a user',
            'input_schema' => [
                'type' => 'object',
                'required' => ['username', 'title', 'message'],
                'properties' => [
                    'username' => [
                        'type' => 'string',
                        'description' => 'Recipient username',
                    ],
                    'title' => [
                        'type' => 'string',
                        'description' => 'Conversation title',
                    ],
                    'message' => [
                        'type' => 'string',
                        'description' => 'Message content',
                    ],
                ],
            ],
        ]);
    }

    public function getToolPromptMeta(): array
    {
        return [
            'getForumList' => [
                'hint'      => 'list all accessible forums — no arguments needed',
                'url_params' => [],
                'post_body' => '{}',
            ],
            'createThread' => [
                'hint'      => 'forum_id (int), title (str), message (str) — all required',
                'url_params' => [],
                'post_body' => '{"forum_id": FORUM_ID, "title": "Thread title", "message": "Content"}',
            ],
            'replyToThread' => [
                'hint'      => 'thread_id (int), message (str) — all required',
                'url_params' => [],
                'post_body' => '{"thread_id": THREAD_ID, "message": "Reply content"}',
            ],
            'editPost' => [
                'hint'      => 'post_id (int), message (str) — all required',
                'url_params' => [],
                'post_body' => '{"post_id": POST_ID, "message": "New post content"}',
            ],
            'sendConversation' => [
                'hint'      => 'username (str), title (str), message (str) — all required',
                'url_params' => [],
                'post_body' => '{"username": "USERNAME", "title": "Subject", "message": "Message"}',
            ],
        ];
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getForumList($params)
    {
        $nodes = \XF::finder('XF:Node')
            ->where('node_type_id', 'Forum')
            ->order('display_order')
            ->fetch();

        $forums = [];
        foreach ($nodes as $node) {
            $forum = $node->Data;
            if (!$forum || !$forum->canView()) {
                continue;
            }

            $forums[] = [
                'forum_id' => $node->node_id,
                'title' => $node->title,
                'description' => $node->description,
                'parent_id' => $node->parent_node_id,
                'thread_count' => $forum->discussion_count ?? 0,
                'post_count' => $forum->message_count ?? 0,
            ];
        }

        return $this->success($forums);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_createThread($params)
    {
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('write')) {
            return $this->error('insufficient_scope', 'The "write" scope is required for this operation');
        }

        $forum = \XF::em()->find('XF:Forum', $params['forum_id']);
        if (!$forum) {
            return $this->error('not_found', 'Forum not found');
        }

        $error = null;
        if (!$forum->canCreateThread($error)) {
            return $this->error('no_permission', 'You do not have permission to post in this forum');
        }

        $creator = \XF::service('XF:Thread\Creator', $forum);
        $creator->setContent($params['title'], $params['message']);

        if (!$creator->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }

        $thread = $creator->save();

        return $this->success([
            'thread_id' => $thread->thread_id,
            'title' => $thread->title,
            'url' => \XF::app()->router('public')->buildLink('canonical:threads', $thread),
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_replyToThread($params)
    {
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('write')) {
            return $this->error('insufficient_scope', 'The "write" scope is required for this operation');
        }

        $thread = \XF::em()->find('XF:Thread', $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }

        $error = null;
        if (!$thread->canReply($error)) {
            return $this->error('no_permission', 'You do not have permission to reply to this thread');
        }

        $replier = \XF::service('XF:Thread\Replier', $thread);
        $replier->setMessage($params['message']);

        if (!$replier->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }

        $post = $replier->save();

        return $this->success([
            'post_id' => $post->post_id,
            'thread_id' => $thread->thread_id,
            'url' => \XF::app()->router('public')->buildLink('canonical:posts', $post),
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_editPost($params)
    {
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('write')) {
            return $this->error('insufficient_scope', 'The "write" scope is required for this operation');
        }

        $post = \XF::em()->find('XF:Post', $params['post_id']);
        if (!$post) {
            return $this->error('not_found', 'Post not found');
        }

        $error = null;
        if (!$post->canEdit($error)) {
            return $this->error('no_permission', 'You do not have permission to edit this post');
        }

        $editor = \XF::service('XF:Post\Editor', $post);
        $editor->setMessage($params['message']);

        if (!$editor->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }

        $editor->save();

        return $this->success([
            'post_id' => $post->post_id,
            'thread_id' => $post->thread_id,
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_sendConversation($params)
    {
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('write')) {
            return $this->error('insufficient_scope', 'The "write" scope is required for this operation');
        }

        $visitor = \XF::visitor();

        $recipient = \XF::em()->findOne('XF:User', ['username' => $params['username']]);
        if (!$recipient) {
            return $this->error('not_found', 'User not found: ' . $params['username']);
        }

        $error = null;
        if (!$visitor->canStartConversation($error)) {
            return $this->error('no_permission', 'You do not have permission to start conversations');
        }

        $creator = \XF::service('XF:Conversation\Creator', $visitor);
        $creator->setRecipients([$recipient->username]);
        $creator->setContent($params['title'], $params['message']);

        if (!$creator->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }

        $conversation = $creator->save();

        return $this->success([
            'conversation_id' => $conversation->conversation_id,
            'title' => $conversation->title,
        ]);
    }
}
