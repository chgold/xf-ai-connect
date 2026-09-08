<?php

namespace chgold\AIConnectPro\Module;

/**
 * Watched & Bookmarks bundle: the connected user's own subscriptions and saved items.
 *
 * All tools are strictly per-visitor — they read from xf_thread_watch,
 * xf_forum_watch, xf_bookmark_item and xf_draft scoped to the authenticated user.
 * Cross-user access would require the admin variants in the Users&Groups
 * Discovery bundle (getUserWatchedThreads, getUserBookmarks — separate).
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProWatchedBookmarksTrait
{
    protected function registerWatchedBookmarksTools()
    {
        $pageLimit = [
            'page' => ['type' => 'integer', 'description' => 'Page number (1-based, default 1)'],
            'limit' => ['type' => 'integer', 'description' => 'Items per page (default 20, cap 100)'],
        ];

        $this->registerTool('getWatchedThreads', [
            'description' => "List threads the current user is watching, with notification preferences and "
                . 'unread state. Returns most-recent activity first.',
            'input_schema' => [
                'type' => 'object',
                'properties' => $pageLimit,
            ],
        ]);

        $this->registerTool('getBookmarkedThreads', [
            'description' => "List the current user's bookmarked threads / posts with labels and dates.",
            'input_schema' => [
                'type' => 'object',
                'properties' => $pageLimit,
            ],
        ]);

        $this->registerTool('listDrafts', [
            'description' => "List the current user's saved drafts (draft type + last update date). "
                . 'Content is not included — use getDraftContent(draft_key) for the body.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getDraftContent', [
            'description' => 'Get the message body of a single saved draft by its draft_key.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['draft_key'],
                'properties' => [
                    'draft_key' => [
                        'type' => 'string',
                        'description' => 'Draft key (e.g. "post-thread-123", "thread-forum-45"). '
                            . 'See listDrafts for available keys.',
                    ],
                ],
            ],
        ]);
    }

    public function execute_getWatchedThreads($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'Log in required');
        }

        $limit = $this->wbBoundedLimit($params);
        $page = isset($params['page']) ? max(1, (int) $params['page']) : 1;

        $finder = \XF::finder('XF:ThreadWatch')
            ->where('user_id', $visitor->user_id)
            ->with(['Thread', 'Thread.Forum'])
            ->order('Thread.last_post_date', 'DESC')
            ->limitByPage($page, $limit);
        $total = $finder->total();
        $watches = $finder->fetch();

        // Unread state is NOT on xf_thread_watch (only user_id, thread_id, email_subscribe).
        // Use the Thread entity's isUnread() which reads xf_thread_user_post + user's read markers.
        $out = [];
        foreach ($watches as $w) {
            if (!$w->Thread || !$w->Thread->canView()) {
                continue;
            }
            $t = $w->Thread;
            $out[] = [
                'thread_id' => (int) $t->thread_id,
                'title' => (string) $t->title,
                'node_id' => (int) $t->node_id,
                'reply_count' => (int) $t->reply_count,
                'last_post_date' => (int) $t->last_post_date,
                'email_subscribe' => (bool) $w->email_subscribe,
                'is_unread' => method_exists($t, 'isUnread') ? (bool) $t->isUnread() : false,
            ];
        }
        return $this->success([
            'threads' => $out,
            'count' => count($out),
            'page' => $page,
            'total' => $total,
            'has_more' => ($page * $limit) < $total,
        ]);
    }

    public function execute_getBookmarkedThreads($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'Log in required');
        }

        $limit = $this->wbBoundedLimit($params);
        $page = isset($params['page']) ? max(1, (int) $params['page']) : 1;

        // xf_bookmark_item is XF 2.2+ (unified bookmarks for threads and posts).
        // Older XF has xf_thread_read only; if BookmarkItem entity is missing we
        // return an empty list with a clear note rather than a hard error.
        if (!\XF::em()->getEntityStructure('XF:BookmarkItem')) {
            return $this->success([
                'bookmarks' => [],
                'count' => 0,
                'note' => 'Bookmarks require XF 2.2+ with the bookmarks feature enabled',
            ]);
        }

        $finder = \XF::finder('XF:BookmarkItem')
            ->where('user_id', $visitor->user_id)
            ->order('bookmark_date', 'DESC')
            ->limitByPage($page, $limit);
        $total = $finder->total();
        $bookmarks = $finder->fetch();

        $out = [];
        foreach ($bookmarks as $b) {
            $out[] = [
                'bookmark_id' => (int) $b->bookmark_id,
                'content_type' => (string) $b->content_type,
                'content_id' => (int) $b->content_id,
                'message' => (string) ($b->message ?? ''),
                'bookmark_date' => (int) $b->bookmark_date,
                'labels' => is_array($b->labels) ? $b->labels : [],
            ];
        }
        return $this->success([
            'bookmarks' => $out,
            'count' => count($out),
            'page' => $page,
            'total' => $total,
            'has_more' => ($page * $limit) < $total,
        ]);
    }

    public function execute_listDrafts($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'Log in required');
        }

        // xf_draft column is `last_update`, not `draft_date` — verified via DESCRIBE xf_draft.
        $drafts = \XF::finder('XF:Draft')
            ->where('user_id', $visitor->user_id)
            ->order('last_update', 'DESC')
            ->fetch();

        $out = [];
        foreach ($drafts as $d) {
            $out[] = [
                'draft_key' => (string) $d->draft_key,
                'last_update' => (int) $d->last_update,
                'message_length' => mb_strlen((string) $d->message),
                'has_extra_data' => !empty($d->extra_data),
            ];
        }
        return $this->success(['drafts' => $out, 'count' => count($out)]);
    }

    public function execute_getDraftContent($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'Log in required');
        }

        $draft = \XF::em()->find('XF:Draft', [
            'user_id' => $visitor->user_id,
            'draft_key' => (string) $params['draft_key'],
        ]);
        if (!$draft) {
            return $this->error('not_found', 'Draft not found');
        }

        return $this->success([
            'draft_key' => (string) $draft->draft_key,
            'message' => (string) $draft->message,
            'extra_data' => is_array($draft->extra_data) ? $draft->extra_data : [],
            'last_update' => (int) $draft->last_update,
        ]);
    }

    private function wbBoundedLimit(array $params): int
    {
        $limit = isset($params['limit']) ? (int) $params['limit'] : 20;
        return max(1, min(100, $limit));
    }
}
