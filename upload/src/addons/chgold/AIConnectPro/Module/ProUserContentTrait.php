<?php

namespace chgold\AIConnectPro\Module;

/**
 * User Content bundle: per-user bookmarks, conversations list, drafts, watched threads,
 * thread attachments, attachment download, and tag search.
 * 7 tools. Read-only except downloadAttachment (streams file bytes).
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProUserContentTrait
{
    protected function registerUserContentTools()
    {
        $page  = ['type' => 'integer', 'description' => 'Page number (1-based, default 1)'];
        $limit = ['type' => 'integer', 'description' => 'Items per page (default 20, max 100)'];

        $this->registerTool('getUserBookmarks', [
            'description' => "List the current user's bookmarks with labels, notes, and dates. "
                . 'Returns bookmark_id, content_type, content_id, title, label, and created_date.',
            'input_schema' => [
                'type' => 'object',
                'properties' => ['page' => $page, 'limit' => $limit],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getUserConversations', [
            'description' => "List the current user's private conversations (title, participants, "
                . 'last reply date, unread state). Does not return message content — '
                . 'use getConversationMessages for that.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'unread_only' => ['type' => 'boolean', 'description' => 'Return only unread conversations (default false)'],
                    'page'  => $page,
                    'limit' => $limit,
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getUserDrafts', [
            'description' => "List the current user's saved drafts with draft_key, content preview, "
                . 'and last_update. Use getDraftContent(draft_key) to retrieve full body.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getUserWatchedThreads', [
            'description' => "List threads the current user is watching, with notification "
                . 'preferences (email/alert) and unread state.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'unread_only' => ['type' => 'boolean', 'description' => 'Return only threads with unread posts (default false)'],
                    'page'  => $page,
                    'limit' => $limit,
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getThreadAttachments', [
            'description' => 'List all attachments across all posts in a thread. '
                . 'Returns filename, size, MIME type, post_id, and attachment_id for each. '
                . 'Metadata only — use downloadAttachment for file content.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['thread_id'],
                'properties' => [
                    'thread_id' => ['type' => 'integer', 'description' => 'Thread to scan for attachments'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('downloadAttachment', [
            'description' => 'Download the content of an attachment as a base64-encoded string. '
                . 'Only available if the current user can view the parent post/message. '
                . 'Use for images, documents, and other files attached to posts.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['attachment_id'],
                'properties' => [
                    'attachment_id' => ['type' => 'integer', 'description' => 'Attachment to download'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('searchTags', [
            'description' => 'Search for threads by tag. Returns threads tagged with the given tag, '
                . 'ordered by last post date. Useful for topic-based discovery.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['tag'],
                'properties' => [
                    'tag'   => ['type' => 'string',  'description' => 'Tag to search for (exact match)'],
                    'page'  => $page,
                    'limit' => $limit,
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    // ── execute methods ──────────────────────────────────────────────────

    public function execute_getUserBookmarks($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('no_permission', 'Must be authenticated');
        }

        $page  = max(1, (int) ($params['page']  ?? 1));
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));

        $bookmarks = \XF::finder('XF:BookmarkItem')
            ->where('user_id', $visitor->user_id)
            ->order('bookmark_date', 'DESC')
            ->limitByPage($page, $limit)
            ->fetch();

        $out = [];
        foreach ($bookmarks as $b) {
            $out[] = [
                'bookmark_id'   => (int)    $b->bookmark_id,
                'content_type'  => (string) $b->content_type,
                'content_id'    => (int)    $b->content_id,
                'title'         => (string) $b->title,
                'label'         => (string) ($b->labels_json ?? ''),
                'bookmark_date' => (int)    $b->bookmark_date,
            ];
        }

        return $this->success(['count' => count($out), 'bookmarks' => $out]);
    }

    public function execute_getUserConversations($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('no_permission', 'Must be authenticated');
        }

        $page       = max(1, (int) ($params['page']  ?? 1));
        $limit      = min(100, max(1, (int) ($params['limit'] ?? 20)));
        $unreadOnly = !empty($params['unread_only']);

        /** @var \XF\Repository\ConversationRepository $repo */
        $repo   = \XF::em()->getRepository('XF:Conversation');
        $finder = $repo->findUserConversations($visitor);
        if ($unreadOnly) {
            $finder->where('Recipient.is_unread', 1);
        }
        $finder->limitByPage($page, $limit);

        $out = [];
        foreach ($finder->fetch() as $c) {
            $out[] = [
                'conversation_id'  => (int)    $c->conversation_id,
                'title'            => (string) $c->title,
                'user_id'          => (int)    $c->user_id,
                'username'         => (string) $c->username,
                'reply_count'      => (int)    $c->reply_count,
                'last_message_date'=> (int)    $c->last_message_date,
                'is_unread'        => (bool)   ($c->Recipient->is_unread ?? false),
            ];
        }

        return $this->success(['count' => count($out), 'conversations' => $out]);
    }

    public function execute_getUserDrafts($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('no_permission', 'Must be authenticated');
        }

        $drafts = \XF::finder('XF:Draft')
            ->where('user_id', $visitor->user_id)
            ->order('last_update', 'DESC')
            ->fetch();

        $out = [];
        foreach ($drafts as $d) {
            $out[] = [
                'draft_key'   => (string) $d->draft_key,
                'preview'     => (string) mb_substr($d->message, 0, 150),
                'last_update' => (int)    $d->last_update,
                'extra_data'  => $d->extra_data,
            ];
        }

        return $this->success(['count' => count($out), 'drafts' => $out]);
    }

    public function execute_getUserWatchedThreads($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('no_permission', 'Must be authenticated');
        }

        $page       = max(1, (int) ($params['page']  ?? 1));
        $limit      = min(100, max(1, (int) ($params['limit'] ?? 20)));
        $unreadOnly = !empty($params['unread_only']);

        /** @var \XF\Repository\ThreadRepository $repo */
        $repo   = \XF::em()->getRepository('XF:Thread');
        $finder = $repo->findThreadsForWatchedList($unreadOnly)
            ->limitByPage($page, $limit);

        $out = [];
        foreach ($finder->fetch() as $t) {
            if (!$t->canView()) continue;
            $watch = $t->Watch[$visitor->user_id] ?? null;
            $out[] = [
                'thread_id'      => (int)    $t->thread_id,
                'title'          => (string) $t->title,
                'last_post_date' => (int)    $t->last_post_date,
                'reply_count'    => (int)    $t->reply_count,
                'notify_on'      => (string) ($watch->email_subscribe ?? 'alert'),
                'is_unread'      => (bool)   ($t->isUnread() ?? false),
            ];
        }

        return $this->success(['count' => count($out), 'threads' => $out]);
    }

    public function execute_getThreadAttachments($params)
    {
        $thread = \XF::em()->find('XF:Thread', (int) $params['thread_id']);
        if (!$thread || !$thread->canView()) {
            return $this->error('not_found', 'Thread not found or not accessible');
        }

        // Fetch all post IDs in thread, then all attachments for those posts
        $postIds = \XF::db()->fetchAllColumn(
            'SELECT post_id FROM xf_post WHERE thread_id=? AND message_state=?',
            [$thread->thread_id, 'visible']
        );

        if (empty($postIds)) {
            return $this->success(['thread_id' => (int) $thread->thread_id, 'count' => 0, 'attachments' => []]);
        }

        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        $rows = \XF::db()->fetchAll(
            "SELECT a.attachment_id, a.filename, a.file_size, a.attach_date,
                    a.view_count, a.content_id AS post_id,
                    ad.width, ad.height
             FROM xf_attachment a
             LEFT JOIN xf_attachment_data ad ON ad.data_id = a.data_id
             WHERE a.content_type='post' AND a.content_id IN ($placeholders)
             ORDER BY a.attach_date ASC",
            $postIds
        );

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'attachment_id' => (int)    $r['attachment_id'],
                'post_id'       => (int)    $r['post_id'],
                'filename'      => (string) $r['filename'],
                'file_size'     => (int)    $r['file_size'],
                'attach_date'   => (int)    $r['attach_date'],
                'view_count'    => (int)    $r['view_count'],
                'width'         => (int)    ($r['width']  ?? 0),
                'height'        => (int)    ($r['height'] ?? 0),
            ];
        }

        return $this->success([
            'thread_id'   => (int) $thread->thread_id,
            'count'       => count($out),
            'attachments' => $out,
        ]);
    }

    public function execute_downloadAttachment($params)
    {
        $attachment = \XF::em()->find('XF:Attachment', (int) $params['attachment_id']);
        if (!$attachment) {
            return $this->error('not_found', 'Attachment not found');
        }

        // Verify the parent post is visible to the current user
        if ($attachment->content_type === 'post') {
            $post = \XF::em()->find('XF:Post', $attachment->content_id);
            if (!$post || !$post->canView()) {
                return $this->error('no_permission', 'Cannot access the parent post');
            }
        }

        $path = \XF::app()->config('internalDataPath')
            . '/attachments/'
            . floor($attachment->data_id / 1000)
            . '/' . $attachment->data_id . '.data';

        if (!file_exists($path)) {
            return $this->error('not_found', 'Attachment file not found on disk');
        }

        $maxBytes = 5 * 1024 * 1024; // 5 MB cap
        $size = filesize($path);
        if ($size > $maxBytes) {
            return $this->error(
                'too_large',
                "Attachment is {$size} bytes — exceeds 5 MB API limit. Download directly via browser."
            );
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return $this->error('read_error', 'Could not read attachment file');
        }

        return $this->success([
            'attachment_id' => (int)    $attachment->attachment_id,
            'filename'      => (string) $attachment->filename,
            'file_size'     => (int)    $attachment->file_size,
            'content_base64'=> base64_encode($contents),
            'encoding'      => 'base64',
        ]);
    }

    public function execute_searchTags($params)
    {
        $tag = trim((string) ($params['tag'] ?? ''));
        if ($tag === '') {
            return $this->error('validation_failed', 'tag is required');
        }

        $page  = max(1, (int) ($params['page']  ?? 1));
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));

        // Find the tag entity
        $tagEntity = \XF::em()->findOne('XF:Tag', ['tag' => $tag]);
        if (!$tagEntity) {
            return $this->success(['tag' => $tag, 'count' => 0, 'threads' => []]);
        }

        // Find tagged content (threads only)
        $rows = \XF::db()->fetchAll(
            'SELECT tc.content_id AS thread_id
             FROM xf_tag_content tc
             WHERE tc.tag_id=? AND tc.content_type=?
             ORDER BY tc.tag_date DESC
             LIMIT ? OFFSET ?',
            [$tagEntity->tag_id, 'thread', $limit, ($page - 1) * $limit]
        );

        $threadIds = array_column($rows, 'thread_id');
        $threads   = \XF::em()->findByIds('XF:Thread', $threadIds);

        $out = [];
        foreach ($threadIds as $tid) {
            $t = $threads[$tid] ?? null;
            if (!$t || !$t->canView()) continue;
            $out[] = [
                'thread_id'      => (int)    $t->thread_id,
                'title'          => (string) $t->title,
                'username'       => (string) $t->username,
                'reply_count'    => (int)    $t->reply_count,
                'last_post_date' => (int)    $t->last_post_date,
            ];
        }

        return $this->success([
            'tag'     => $tag,
            'page'    => $page,
            'limit'   => $limit,
            'count'   => count($out),
            'threads' => $out,
        ]);
    }
}
