<?php

namespace chgold\AIConnectPro\Module;

/**
 * Thread Content bundle: read posts/threads by container + poll results.
 * 4 tools (moved from Free planned → Pro, decision 2026-09-10).
 *
 * All tools are read-only, require only the 'read' scope.
 * XF permission checks (canView) are applied on every entity.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProThreadContentTrait
{
    protected function registerThreadContentTools()
    {
        $page = ['type' => 'integer', 'description' => 'Page number (1-based, default 1)'];
        $limit = ['type' => 'integer', 'description' => 'Items per page (default 20, max 100)'];

        $this->registerTool('getPostsByThread', [
            'description' => 'Retrieve posts within a thread with pagination. Returns post content, '
                . 'author, timestamp, reaction count, and attachment metadata.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread to read'],
                    'page'  => $page,
                    'limit' => $limit,
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getThreadsByForum', [
            'description' => 'List threads in a forum/node with optional filters (sticky, prefix, date). '
                . 'Returns title, author, reply count, view count, last post info.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => [
                    'node_id'    => ['type' => 'integer', 'description' => 'Forum node ID'],
                    'sticky_only' => ['type' => 'boolean', 'description' => 'Return only sticky threads (default false)'],
                    'prefix_id'  => ['type' => 'integer', 'description' => 'Filter by thread prefix ID (optional)'],
                    'order'      => ['type' => 'string',  'description' => 'Sort: last_post_date (default), post_date, reply_count, view_count'],
                    'page'  => $page,
                    'limit' => $limit,
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getThreadPollResults', [
            'description' => 'Retrieve poll results for a thread: options, vote counts, percentages, '
                . 'total voters, close date, and whether the current user has voted. '
                . 'Returns not_found if the thread has no poll.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread whose poll to read'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getAttachmentsByPost', [
            'description' => 'List all attachments on a specific post: filename, size, MIME type, '
                . 'upload date, view count, and attachment ID. Metadata only — '
                . 'use downloadAttachment for file content.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id'],
                'properties' => [
                    'post_id' => ['type' => 'integer', 'description' => 'Post whose attachments to list'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    // ── execute methods ──────────────────────────────────────────────────

    public function execute_getPostsByThread($params)
    {
        $thread = \XF::em()->find('XF:Thread', (int) $params['thread_id']);
        if (!$thread || !$thread->canView()) {
            return $this->error('not_found', 'Thread not found or not accessible');
        }

        $page  = max(1, (int) ($params['page']  ?? 1));
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));

        /** @var \XF\Repository\PostRepository $repo */
        $repo = \XF::em()->getRepository('XF:Post');
        $finder = $repo->findPostsForThreadView($thread)
            ->limitByPage($page, $limit);

        $posts = [];
        foreach ($finder->fetch() as $post) {
            if (!$post->canView()) {
                continue;
            }
            $posts[] = [
                'post_id'        => (int)    $post->post_id,
                'thread_id'      => (int)    $post->thread_id,
                'user_id'        => (int)    $post->user_id,
                'username'       => (string) $post->username,
                'post_date'      => (int)    $post->post_date,
                'message'        => (string) $post->message,
                'reaction_score' => (int)    $post->reaction_score,
                'attach_count'   => (int)    $post->attach_count,
                'position'       => (int)    $post->position,
            ];
        }

        return $this->success([
            'thread_id'  => (int) $thread->thread_id,
            'title'      => (string) $thread->title,
            'page'       => $page,
            'limit'      => $limit,
            'count'      => count($posts),
            'posts'      => $posts,
        ]);
    }

    public function execute_getThreadsByForum($params)
    {
        // XF:Forum uses node_id as PK but is stored in xf_forum, not xf_node.
        // findOne via Node relation is the correct approach.
        $node = \XF::em()->find('XF:Node', (int) $params['node_id']);
        if (!$node || $node->node_type_id !== 'Forum') {
            return $this->error('not_found', 'Forum node not found');
        }
        $forum = $node->getDataRelationOrDefault();
        if (!$forum || !$node->canView()) {
            return $this->error('not_found', 'Forum not found or not accessible');
        }

        $page  = max(1, (int) ($params['page']  ?? 1));
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));

        /** @var \XF\Repository\ThreadRepository $repo */
        $repo = \XF::em()->getRepository('XF:Thread');
        $finder = $repo->findThreadsForForumView($forum)
            ->limitByPage($page, $limit);

        if (!empty($params['sticky_only'])) {
            $finder->where('sticky', 1);
        }
        if (!empty($params['prefix_id'])) {
            $finder->where('prefix_id', (int) $params['prefix_id']);
        }

        $orderMap = [
            'last_post_date' => 'last_post_date',
            'post_date'      => 'post_date',
            'reply_count'    => 'reply_count',
            'view_count'     => 'view_count',
        ];
        $order = $orderMap[$params['order'] ?? ''] ?? 'last_post_date';
        $finder->order($order, 'DESC');

        $threads = [];
        foreach ($finder->fetch() as $t) {
            if (!$t->canView()) {
                continue;
            }
            $threads[] = [
                'thread_id'      => (int)    $t->thread_id,
                'title'          => (string) $t->title,
                'user_id'        => (int)    $t->user_id,
                'username'       => (string) $t->username,
                'post_date'      => (int)    $t->post_date,
                'last_post_date' => (int)    $t->last_post_date,
                'reply_count'    => (int)    $t->reply_count,
                'view_count'     => (int)    $t->view_count,
                'sticky'         => (bool)   $t->sticky,
                'prefix_id'      => (int)    $t->prefix_id,
            ];
        }

        return $this->success([
            'node_id' => (int) $forum->node_id,
            'title'   => (string) $forum->title,
            'page'    => $page,
            'limit'   => $limit,
            'count'   => count($threads),
            'threads' => $threads,
        ]);
    }

    public function execute_getThreadPollResults($params)
    {
        $thread = \XF::em()->find('XF:Thread', (int) $params['thread_id']);
        if (!$thread || !$thread->canView()) {
            return $this->error('not_found', 'Thread not found or not accessible');
        }

        $poll = \XF::em()->find('XF:Poll', $thread->thread_id);
        if (!$poll) {
            return $this->error('not_found', 'This thread has no poll');
        }

        $visitor   = \XF::visitor();
        $totalVotes = 0;
        $responses  = [];
        foreach ($poll->Responses as $r) {
            $totalVotes += $r->response_vote_count;
        }
        foreach ($poll->Responses as $r) {
            $pct = $totalVotes > 0
                ? round($r->response_vote_count / $totalVotes * 100, 1)
                : 0.0;
            $responses[] = [
                'response_id'  => (int)    $r->poll_response_id,
                'response'     => (string) $r->response,
                'vote_count'   => (int)    $r->response_vote_count,
                'percentage'   => $pct,
            ];
        }

        $hasVoted = $visitor->user_id
            ? (bool) \XF::db()->fetchOne(
                'SELECT COUNT(*) FROM xf_poll_vote WHERE poll_id=? AND user_id=?',
                [$poll->poll_id, $visitor->user_id]
            )
            : false;

        return $this->success([
            'poll_id'      => (int)    $poll->poll_id,
            'question'     => (string) $poll->question,
            'total_votes'  => $totalVotes,
            'close_date'   => (int)    $poll->close_date,
            'closed'       => (bool)   $poll->isClosed(),
            'has_voted'    => $hasVoted,
            'responses'    => $responses,
        ]);
    }

    public function execute_getAttachmentsByPost($params)
    {
        $post = \XF::em()->find('XF:Post', (int) $params['post_id']);
        if (!$post || !$post->canView()) {
            return $this->error('not_found', 'Post not found or not accessible');
        }

        $attachments = \XF::em()->findByIds(
            'XF:Attachment',
            \XF::db()->fetchAllColumn(
                'SELECT attachment_id FROM xf_attachment
                 WHERE content_type=? AND content_id=?
                 ORDER BY attach_date ASC',
                ['post', $post->post_id]
            )
        );

        $out = [];
        foreach ($attachments as $a) {
            $out[] = [
                'attachment_id' => (int)    $a->attachment_id,
                'filename'      => (string) $a->filename,
                'file_size'     => (int)    $a->file_size,
                'content_type'  => (string) $a->Data->file_hash ?? '',
                'attach_date'   => (int)    $a->attach_date,
                'view_count'    => (int)    $a->view_count,
                'width'         => (int)    ($a->Data->width  ?? 0),
                'height'        => (int)    ($a->Data->height ?? 0),
            ];
        }

        return $this->success([
            'post_id'     => (int) $post->post_id,
            'thread_id'   => (int) $post->thread_id,
            'count'       => count($out),
            'attachments' => $out,
        ]);
    }
}
