<?php

namespace chgold\AIConnectPro\Module;

/**
 * Activity & Analytics bundle: usage patterns, unanswered content, engagement counters.
 *
 * These read xf_thread, xf_post, xf_reaction_content, xf_thread_watch and
 * xf_user_alert with time-window filters — the raw materials for "who is active,
 * what's trending, what needs a reply" reports.
 *
 * Every finder is time-bounded (default 30 days) and every list is paginated
 * so no single call can pull the whole database.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProActivityAnalyticsTrait
{
    protected function registerActivityAnalyticsTools()
    {
        $this->registerTool('getUserActivity', [
            'description' => "Recent posts + threads authored by a user within a time window. "
                . 'Useful for engagement audits and user-scoped moderation.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'Target user ID'],
                    'days' => ['type' => 'integer', 'description' => 'Window in days (default 30, cap 365)'],
                    'limit' => ['type' => 'integer', 'description' => 'Max items per section (default 20, cap 100)'],
                ],
            ],
        ]);

        $this->registerTool('getForumActivity', [
            'description' => 'Activity metrics for one forum node in a time window: new threads, '
                . 'new posts, unique posters, top contributors.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => [
                    'node_id' => ['type' => 'integer', 'description' => 'Forum node ID'],
                    'days' => ['type' => 'integer', 'description' => 'Window in days (default 7, cap 365)'],
                ],
            ],
        ]);

        $this->registerTool('getUnansweredThreads', [
            'description' => 'List threads with zero replies within a scope (globally or in one node). '
                . 'Prioritises support forums and QA.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'node_id' => ['type' => 'integer', 'description' => 'Restrict to one forum (optional)'],
                    'days' => ['type' => 'integer', 'description' => 'Max age in days (default 30, cap 365)'],
                    'limit' => ['type' => 'integer', 'description' => 'Max results (default 20, cap 100)'],
                ],
            ],
        ]);

        $this->registerTool('getThreadReactions', [
            'description' => 'Aggregate reactions across all posts within a thread — total count '
                . 'and breakdown by reaction type.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread ID'],
                ],
            ],
        ]);

        $this->registerTool('getThreadWatchers', [
            'description' => 'List users watching a specific thread (visible to thread author + moderators only).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread ID'],
                    'limit' => ['type' => 'integer', 'description' => 'Max watchers (default 50, cap 200)'],
                ],
            ],
        ]);
    }

    public function execute_getUserActivity($params)
    {
        $user = \XF::em()->find('XF:User', (int) $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }
        if (!\XF::visitor()->canViewMemberList()) {
            return $this->error('no_permission', 'You lack permission to view member activity');
        }

        $days = isset($params['days']) ? max(1, min(365, (int) $params['days'])) : 30;
        $limit = isset($params['limit']) ? max(1, min(100, (int) $params['limit'])) : 20;
        $cutoff = \XF::$time - ($days * 86400);

        $posts = \XF::finder('XF:Post')
            ->where('user_id', $user->user_id)
            ->where('post_date', '>', $cutoff)
            ->where('message_state', 'visible')
            ->with('Thread')
            ->order('post_date', 'DESC')
            ->limit($limit)
            ->fetch();

        $threads = \XF::finder('XF:Thread')
            ->where('user_id', $user->user_id)
            ->where('post_date', '>', $cutoff)
            ->where('discussion_state', 'visible')
            ->order('post_date', 'DESC')
            ->limit($limit)
            ->fetch();

        $postOut = [];
        foreach ($posts as $p) {
            if (!$p->canView()) {
                continue;
            }
            $postOut[] = [
                'post_id' => (int) $p->post_id,
                'thread_id' => (int) $p->thread_id,
                'thread_title' => $p->Thread ? $p->Thread->title : null,
                'post_date' => (int) $p->post_date,
                'message_preview' => mb_substr(strip_tags((string) $p->message), 0, 120),
            ];
        }

        $threadOut = [];
        foreach ($threads as $t) {
            if (!$t->canView()) {
                continue;
            }
            $threadOut[] = [
                'thread_id' => (int) $t->thread_id,
                'title' => (string) $t->title,
                'node_id' => (int) $t->node_id,
                'reply_count' => (int) $t->reply_count,
                'view_count' => (int) $t->view_count,
                'post_date' => (int) $t->post_date,
            ];
        }

        return $this->success([
            'user_id' => (int) $user->user_id,
            'username' => (string) $user->username,
            'window_days' => $days,
            'posts_authored' => $postOut,
            'threads_started' => $threadOut,
            'post_count_in_window' => count($postOut),
            'thread_count_in_window' => count($threadOut),
        ]);
    }

    public function execute_getForumActivity($params)
    {
        $node = \XF::em()->find('XF:Node', (int) $params['node_id']);
        if (!$node || !$node->canView()) {
            return $this->error('not_found', 'Forum node not found');
        }

        $days = isset($params['days']) ? max(1, min(365, (int) $params['days'])) : 7;
        $cutoff = \XF::$time - ($days * 86400);

        $db = \XF::db();
        $newThreads = (int) $db->fetchOne(
            'SELECT COUNT(*) FROM xf_thread WHERE node_id = ? AND post_date > ? AND discussion_state = ?',
            [$node->node_id, $cutoff, 'visible']
        );
        $newPosts = (int) $db->fetchOne(
            'SELECT COUNT(p.post_id) FROM xf_post p
             INNER JOIN xf_thread t ON t.thread_id = p.thread_id
             WHERE t.node_id = ? AND p.post_date > ? AND p.message_state = ?',
            [$node->node_id, $cutoff, 'visible']
        );
        $uniquePosters = (int) $db->fetchOne(
            'SELECT COUNT(DISTINCT p.user_id) FROM xf_post p
             INNER JOIN xf_thread t ON t.thread_id = p.thread_id
             WHERE t.node_id = ? AND p.post_date > ? AND p.message_state = ? AND p.user_id > 0',
            [$node->node_id, $cutoff, 'visible']
        );
        $topContributors = $db->fetchAll(
            'SELECT p.user_id, u.username, COUNT(p.post_id) AS post_count
             FROM xf_post p
             INNER JOIN xf_thread t ON t.thread_id = p.thread_id
             INNER JOIN xf_user u ON u.user_id = p.user_id
             WHERE t.node_id = ? AND p.post_date > ? AND p.message_state = ? AND p.user_id > 0
             GROUP BY p.user_id, u.username
             ORDER BY post_count DESC
             LIMIT 10',
            [$node->node_id, $cutoff, 'visible']
        );

        return $this->success([
            'node_id' => (int) $node->node_id,
            'title' => (string) $node->title,
            'window_days' => $days,
            'new_threads' => $newThreads,
            'new_posts' => $newPosts,
            'unique_posters' => $uniquePosters,
            'top_contributors' => $topContributors,
        ]);
    }

    public function execute_getUnansweredThreads($params)
    {
        $days = isset($params['days']) ? max(1, min(365, (int) $params['days'])) : 30;
        $limit = isset($params['limit']) ? max(1, min(100, (int) $params['limit'])) : 20;
        $cutoff = \XF::$time - ($days * 86400);

        $finder = \XF::finder('XF:Thread')
            ->where('reply_count', 0)
            ->where('post_date', '>', $cutoff)
            ->where('discussion_state', 'visible')
            ->order('post_date', 'DESC')
            ->limit($limit);
        if (!empty($params['node_id'])) {
            $finder->where('node_id', (int) $params['node_id']);
        }

        $out = [];
        foreach ($finder->fetch() as $t) {
            if (!$t->canView()) {
                continue;
            }
            $out[] = [
                'thread_id' => (int) $t->thread_id,
                'title' => (string) $t->title,
                'node_id' => (int) $t->node_id,
                'user_id' => (int) $t->user_id,
                'username' => (string) $t->username,
                'post_date' => (int) $t->post_date,
                'view_count' => (int) $t->view_count,
                'age_days' => (int) round((\XF::$time - $t->post_date) / 86400),
            ];
        }
        return $this->success([
            'threads' => $out,
            'count' => count($out),
            'window_days' => $days,
        ]);
    }

    public function execute_getThreadReactions($params)
    {
        $thread = \XF::em()->find('XF:Thread', (int) $params['thread_id']);
        if (!$thread || !$thread->canView()) {
            return $this->error('not_found', 'Thread not found or not accessible');
        }

        // xf_reaction stores no `title` column — display name is a phrase (reaction_title.N)
        // resolved via the Entity, so fetch the reaction_id counts here and enrich
        // titles via em()->find. emoji_shortname is a real column and useful as a stable
        // secondary label alongside the (localised) title.
        $db = \XF::db();
        $rows = $db->fetchAll(
            'SELECT rc.reaction_id, r.emoji_shortname, COUNT(*) AS reaction_count
             FROM xf_reaction_content rc
             INNER JOIN xf_post p ON p.post_id = rc.content_id AND rc.content_type = ?
             LEFT JOIN xf_reaction r ON r.reaction_id = rc.reaction_id
             WHERE p.thread_id = ? AND rc.is_counted = 1
             GROUP BY rc.reaction_id, r.emoji_shortname
             ORDER BY reaction_count DESC',
            ['post', $thread->thread_id]
        );
        // Enrich each row with the localised title from the Reaction entity.
        foreach ($rows as &$row) {
            $reaction = \XF::em()->find('XF:Reaction', (int) $row['reaction_id']);
            $row['title'] = $reaction ? (string) $reaction->title : null;
        }
        unset($row);

        $total = 0;
        foreach ($rows as $r) {
            $total += (int) $r['reaction_count'];
        }

        return $this->success([
            'thread_id' => (int) $thread->thread_id,
            'title' => (string) $thread->title,
            'total_reactions' => $total,
            'by_type' => $rows,
        ]);
    }

    public function execute_getThreadWatchers($params)
    {
        $thread = \XF::em()->find('XF:Thread', (int) $params['thread_id']);
        if (!$thread) {
            return $this->error('not_found', 'Thread not found');
        }

        // Gate: thread author OR moderator/admin. Watching lists are private.
        $visitor = \XF::visitor();
        $isOwner = $visitor->user_id && $visitor->user_id === (int) $thread->user_id;
        if (!$isOwner && !$visitor->is_moderator && !$visitor->is_admin) {
            return $this->error('no_permission', 'Only the thread author or a moderator may see watchers');
        }

        $limit = isset($params['limit']) ? max(1, min(200, (int) $params['limit'])) : 50;
        $watches = \XF::finder('XF:ThreadWatch')
            ->where('thread_id', $thread->thread_id)
            ->with('User')
            ->order('email_subscribe', 'DESC')
            ->limit($limit)
            ->fetch();

        $out = [];
        foreach ($watches as $w) {
            if (!$w->User) {
                continue;
            }
            $out[] = [
                'user_id' => (int) $w->user_id,
                'username' => (string) $w->User->username,
                'email_subscribe' => (bool) $w->email_subscribe,
            ];
        }
        return $this->success([
            'thread_id' => (int) $thread->thread_id,
            'watchers' => $out,
            'count' => count($out),
        ]);
    }
}
