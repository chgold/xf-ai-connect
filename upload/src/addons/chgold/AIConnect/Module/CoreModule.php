<?php

namespace chgold\AIConnect\Module;

class CoreModule extends ModuleBase
{
    protected $moduleName = 'xenforo';

    protected function registerTools()
    {
        $this->registerTool('searchThreads', [
            'description' => 'Search XenForo threads with filters',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'search' => [
                        'type' => 'string',
                        'description' => 'Search query',
                    ],
                    'forum_id' => [
                        'type' => 'integer',
                        'description' => 'Forum ID to filter by',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of threads',
                        'default' => 10,
                    ],
                ],
            ],
        ]);

        $this->registerTool('getThread', [
            'description' => 'Get a single thread by ID',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => [
                        'type' => 'integer',
                        'description' => 'Thread ID',
                    ],
                ],
            ],
        ]);

        $this->registerTool('searchPosts', [
            'description' => 'Search XenForo posts',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'search' => [
                        'type' => 'string',
                        'description' => 'Search query',
                    ],
                    'thread_id' => [
                        'type' => 'integer',
                        'description' => 'Thread ID to filter by',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum number of posts',
                        'default' => 10,
                    ],
                ],
            ],
        ]);

        $this->registerTool('getPost', [
            'description' => 'Get a single post by ID',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id'],
                'properties' => [
                    'post_id' => [
                        'type' => 'integer',
                        'description' => 'Post ID',
                    ],
                ],
            ],
        ]);

        $this->registerTool('getCurrentUser', [
            'description' => 'Get current authenticated user information',
            'input_schema' => [
                'type' => 'object',
                'properties' => [],
            ],
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_searchThreads($params)
    {
        $finder = \XF::finder('XF:Thread');

        if (!empty($params['search'])) {
            $finder->where('title', 'LIKE', '%' . $params['search'] . '%');
        }

        if (!empty($params['forum_id'])) {
            $finder->where('node_id', $params['forum_id']);
        }

        $limit = $params['limit'] ?? 10;

        $finder->where('discussion_state', 'visible')
            ->with('Forum')
            ->order('post_date', 'DESC')
            ->limit($limit * 2);

        $threads = $finder->fetch();
        $result = [];

        foreach ($threads as $thread) {
            if (!$thread->canView()) {
                continue;
            }
            $result[] = $this->formatThread($thread);
            if (count($result) >= $limit) {
                break;
            }
        }

        return $this->success($result);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getThread($params)
    {
        $thread = \XF::em()->find('XF:Thread', $params['thread_id'], ['Forum']);

        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }

        if (!$thread->canView()) {
            return $this->error('not_accessible', 'Thread is not accessible');
        }

        return $this->success($this->formatThread($thread));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_searchPosts($params)
    {
        $finder = \XF::finder('XF:Post');

        if (!empty($params['search'])) {
            $finder->where('message', 'LIKE', '%' . $params['search'] . '%');
        }

        if (!empty($params['thread_id'])) {
            $finder->where('thread_id', $params['thread_id']);
        }

        $limit = $params['limit'] ?? 10;

        $finder->where('message_state', 'visible')
            ->with(['Thread', 'Thread.Forum'])
            ->order('post_date', 'DESC')
            ->limit($limit * 2);

        $posts = $finder->fetch();
        $result = [];

        foreach ($posts as $post) {
            if (!$post->canView()) {
                continue;
            }
            $result[] = $this->formatPost($post);
            if (count($result) >= $limit) {
                break;
            }
        }

        return $this->success($result);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getPost($params)
    {
        $post = \XF::em()->find('XF:Post', $params['post_id'], ['Thread', 'Thread.Forum']);

        if (!$post) {
            return $this->error('not_found', 'Post not found');
        }

        if (!$post->canView()) {
            return $this->error('not_accessible', 'Post is not accessible');
        }

        return $this->success($this->formatPost($post));
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Called dynamically via dispatch: 'execute_' . $name in ModuleBase
    public function execute_getCurrentUser($params)
    {
        $visitor = \XF::visitor();

        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'No authenticated user');
        }

        return $this->success([
            'user_id' => $visitor->user_id,
            'username' => $visitor->username,
            'email' => $visitor->email,
            'is_admin' => $visitor->is_admin,
            'is_moderator' => $visitor->is_moderator,
            'message_count' => $visitor->message_count,
            'register_date' => $visitor->register_date,
        ]);
    }

    protected function formatThread($thread)
    {
        return [
            'thread_id' => $thread->thread_id,
            'title' => $thread->title,
            'forum_id' => $thread->node_id,
            'forum_name' => $thread->Forum ? $thread->Forum->title : null,
            'user_id' => $thread->user_id,
            'username' => $thread->username,
            'post_date' => date('c', $thread->post_date),
            'reply_count' => $thread->reply_count,
            'view_count' => $thread->view_count,
            'last_post_date' => date('c', $thread->last_post_date),
            'prefix_id' => $thread->prefix_id,
            'discussion_state' => $thread->discussion_state,
            'url' => \XF::app()->router('public')->buildLink('canonical:threads', $thread),
        ];
    }

    protected function formatPost($post)
    {
        return [
            'post_id' => $post->post_id,
            'thread_id' => $post->thread_id,
            'user_id' => $post->user_id,
            'username' => $post->username,
            'post_date' => date('c', $post->post_date),
            'message' => $post->message,
            'message_state' => $post->message_state,
            'thread_title' => $post->Thread ? $post->Thread->title : null,
            'url' => \XF::app()->router('public')->buildLink('canonical:posts', $post),
        ];
    }
}
