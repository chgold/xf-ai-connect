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
                    'user_id' => [
                        'type' => 'integer',
                        'description' => 'Filter by author user ID (optional)',
                    ],
                    'username' => [
                        'type' => 'string',
                        'description' => 'Filter by author username, case-insensitive (optional)',
                    ],
                    'since' => [
                        'type' => 'string',
                        'description' => 'Time filter. Presets: today, yesterday, 1hour, 1week, 1month. Any relative duration as <N><unit> where unit=h/d/w/m/y (e.g. 3w, 21d, 6h, 3months, 2years — any number + any unit works). Exact date YYYY-MM-DD. Or "all" for full history. Unknown values fall back to all history.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date: ISO format YYYY-MM-DD (e.g. "2026-03-08") or Unix timestamp as string. Optional.',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date: ISO format YYYY-MM-DD (e.g. "2026-03-29") or Unix timestamp as string. Optional.',
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
                    'user_id' => [
                        'type' => 'integer',
                        'description' => 'Filter by author user ID (optional)',
                    ],
                    'username' => [
                        'type' => 'string',
                        'description' => 'Filter by author username, case-insensitive (optional)',
                    ],
                    'since' => [
                        'type' => 'string',
                        'description' => 'Time filter. Presets: today, yesterday, 1hour, 1week, 1month. Any relative duration as <N><unit> where unit=h/d/w/m/y (e.g. 3w, 21d, 6h, 3months, 2years — any number + any unit works). Exact date YYYY-MM-DD. Or "all" for full history. Unknown values fall back to all history.',
                    ],
                    'date_from' => [
                        'type' => 'string',
                        'description' => 'Start date: ISO format YYYY-MM-DD (e.g. "2026-03-08") or Unix timestamp as string. Optional.',
                    ],
                    'date_to' => [
                        'type' => 'string',
                        'description' => 'End date: ISO format YYYY-MM-DD (e.g. "2026-03-29") or Unix timestamp as string. Optional.',
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
                'properties' => new \stdClass(),
            ],
        ]);
    }

    protected function resolveDateFrom($params)
    {
        if (!empty($params['date_from'])) {
            return $this->parseTimestamp($params['date_from']);
        }
        if (!empty($params['since'])) {
            return $this->parseSince($params['since']);
        }
        return null;
    }

    protected function parseTimestamp($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        // ISO date "YYYY-MM-DD"
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
            $ts = strtotime($value . ' 00:00:00');
            return $ts !== false ? $ts : (int) $value;
        }
        // ISO datetime "YYYY-MM-DD HH:MM"
        if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', (string) $value)) {
            $ts = strtotime($value);
            return $ts !== false ? $ts : (int) $value;
        }
        return (int) $value;
    }

    protected function parseSince($since)
    {
        $now = time();
        switch ($since) {
            case 'today':
                return mktime(0, 0, 0);
            case 'yesterday':
                return mktime(0, 0, 0) - 86400;
            case '1hour':
                return $now - 3600;
            case '1week':
                return $now - 604800;
            case '1month':
                return $now - 2592000;
            case 'all':
            case 'everything':
            case 'all-time':
            case 'alltime':
                return 0; // Unix epoch = all history
        }
        // Dynamic patterns: "3d", "6h", "2w", "1m", "1y", "3days", "6hours", "2years" etc.
        if (preg_match('/^(\d+)\s*(d|h|w|m|y|day|hour|week|month|year)s?$/i', $since, $matches)) {
            $n    = (int) $matches[1];
            $unit = strtolower($matches[2][0]);
            switch ($unit) {
                case 'd':
                    return $now - ($n * 86400);
                case 'h':
                    return $now - ($n * 3600);
                case 'w':
                    return $now - ($n * 604800);
                case 'm':
                    return $now - ($n * 2592000);
                case 'y':
                    return $now - ($n * 31536000);
            }
        }
        // ISO date "YYYY-MM-DD"
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $since)) {
            $ts = strtotime($since . ' 00:00:00');
            return $ts !== false ? $ts : null;
        }
        // ISO datetime
        if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', $since)) {
            $ts = strtotime($since);
            return $ts !== false ? $ts : null;
        }
        // Unknown value → return null = no date filter (return all history)
        return null;
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

        if (!empty($params['user_id'])) {
            $finder->where('user_id', (int) $params['user_id']);
        }
        if (!empty($params['username'])) {
            $finder->where('username', $params['username']);
        }

        $dateFrom = $this->resolveDateFrom($params);
        if ($dateFrom !== null) {
            $finder->where('post_date', '>=', $dateFrom);
        }
        if (!empty($params['date_to'])) {
            $finder->where('post_date', '<=', $this->parseTimestamp($params['date_to']));
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
        $thread = \XF::em()->find('XF:Thread', $params['thread_id'], ['Forum', 'FirstPost']);

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

        if (!empty($params['user_id'])) {
            $finder->where('user_id', (int) $params['user_id']);
        }
        if (!empty($params['username'])) {
            $finder->where('username', $params['username']);
        }

        $dateFrom = $this->resolveDateFrom($params);
        if ($dateFrom !== null) {
            $finder->where('post_date', '>=', $dateFrom);
        }
        if (!empty($params['date_to'])) {
            $finder->where('post_date', '<=', $this->parseTimestamp($params['date_to']));
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
            'discussion_state'   => $thread->discussion_state,
            'first_post_id'      => $thread->first_post_id,
            'first_post_message' => $thread->FirstPost ? $thread->FirstPost->message : null,
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
