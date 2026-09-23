<?php

namespace chgold\AIConnectModeration\Module;

/**
 * Moderation Queue bundle: 10 tools — queue/history/stats/spam intelligence
 * plus assignModerator + deleteSpam. Read tools surface the moderation queue and
 * reports; the two write/delete tools act only within the member's moderator
 * rights (every handler re-checks XF permissions).
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProModQueueTrait
{
    protected function registerModQueueTools()
    {
        $this->registerTool('getModerationQueue', [
            'description' => 'List items awaiting moderation (the approval queue) with content type, id and author.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'content_type' => ['type' => 'string', 'description' => 'Filter by content type (post/thread/...) — optional'],
                    'limit' => ['type' => 'integer', 'description' => 'Max results (default 20, max 100)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getModerationItem', [
            'description' => 'Get one moderation-queue item by its content type + id.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['content_type', 'content_id'],
                'properties' => [
                    'content_type' => ['type' => 'string'],
                    'content_id' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('searchModerationQueue', [
            'description' => 'Search the moderation queue by author username and/or content type.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'username' => ['type' => 'string', 'description' => 'Filter by author username'],
                    'content_type' => ['type' => 'string'],
                    'limit' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getModerationStats', [
            'description' => 'Counts for the moderation workload: pending approval items + open/assigned reports.',
            'input_schema' => ['type' => 'object', 'properties' => [], 'additionalProperties' => false],
        ]);

        $this->registerTool('getModerationHistory', [
            'description' => 'Recent moderator actions from the moderator log (who did what, when).',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'limit' => ['type' => 'integer', 'description' => 'Max rows (default 20, max 100)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getReportedContent', [
            'description' => 'Resolve a report to the actual reported content (title/message excerpt + author).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['report_id'],
                'properties' => ['report_id' => ['type' => 'integer']],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getReportComments', [
            'description' => 'Get the comment/audit thread of a specific report.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['report_id'],
                'properties' => ['report_id' => ['type' => 'integer']],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getSpamPatterns', [
            'description' => 'Recent spam-trigger / spam-cleanup signals: users recently flagged as spammers.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'limit' => ['type' => 'integer', 'description' => 'Max rows (default 20, max 100)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('assignModerator', [
            'description' => 'Assign an open report to a moderator (self or another) — requires report-management rights.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['report_id', 'assign_user_id'],
                'properties' => [
                    'report_id' => ['type' => 'integer'],
                    'assign_user_id' => ['type' => 'integer', 'description' => 'The moderator user id to assign to'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('deleteSpam', [
            'description' => 'Delete a post/thread identified as spam (soft-delete with a spam reason) — requires delete rights on the content.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['content_type', 'content_id'],
                'properties' => [
                    'content_type' => ['type' => 'string', 'description' => 'post or thread'],
                    'content_id' => ['type' => 'integer'],
                    'reason' => ['type' => 'string', 'description' => 'Optional deletion reason (default "Spam")'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    // ── read handlers ─────────────────────────────────────────────────────────

    public function execute_getModerationQueue($params)
    {
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));
        $repo = \XF::em()->getRepository('XF:ApprovalQueue');
        $finder = $repo->findUnapprovedContent()->limit($limit);
        $out = [];
        foreach ($finder->fetch() as $item) {
            $content = $item->Content;
            if (!$content && !empty($params['content_type'])) {
                continue;
            }
            $type = (string) $item->content_type;
            if (!empty($params['content_type']) && $type !== $params['content_type']) {
                continue;
            }
            $out[] = [
                'content_type' => $type,
                'content_id'   => (int) $item->content_id,
                'content_date' => (int) $item->content_date,
                'title'        => $content ? (string) ($content->getContentTitle('approval') ?? '') : '',
                'user_id'      => $content && isset($content->user_id) ? (int) $content->user_id : 0,
            ];
        }
        return $this->success(['count' => count($out), 'items' => $out]);
    }

    public function execute_getModerationItem($params)
    {
        $type = (string) $params['content_type'];
        $id   = (int) $params['content_id'];
        $repo = \XF::em()->getRepository('XF:ApprovalQueue');
        foreach ($repo->findUnapprovedContent()->fetch() as $item) {
            if ((string) $item->content_type === $type && (int) $item->content_id === $id) {
                $content = $item->Content;
                return $this->success([
                    'content_type' => $type,
                    'content_id'   => $id,
                    'content_date' => (int) $item->content_date,
                    'title'        => $content ? (string) ($content->getContentTitle('approval') ?? '') : '',
                    'found'        => true,
                ]);
            }
        }
        return $this->error('not_found', 'Item not in the moderation queue');
    }

    public function execute_searchModerationQueue($params)
    {
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));
        $username = trim((string) ($params['username'] ?? ''));
        $typeFilter = trim((string) ($params['content_type'] ?? ''));
        $repo = \XF::em()->getRepository('XF:ApprovalQueue');
        $out = [];
        foreach ($repo->findUnapprovedContent()->fetch() as $item) {
            $type = (string) $item->content_type;
            if ($typeFilter !== '' && $type !== $typeFilter) {
                continue;
            }
            $content = $item->Content;
            if ($username !== '') {
                $u = $content && $content->User ? (string) $content->User->username : '';
                if (mb_stripos($u, $username) === false) {
                    continue;
                }
            }
            $out[] = [
                'content_type' => $type,
                'content_id'   => (int) $item->content_id,
                'username'     => $content && $content->User ? (string) $content->User->username : '',
            ];
            if (count($out) >= $limit) {
                break;
            }
        }
        return $this->success(['count' => count($out), 'items' => $out]);
    }

    public function execute_getModerationStats($params)
    {
        $db = \XF::db();
        $pending = (int) $db->fetchOne('SELECT COUNT(*) FROM xf_approval_queue');
        $reportsOpen = (int) $db->fetchOne("SELECT COUNT(*) FROM xf_report WHERE report_state = 'open'");
        $reportsAssigned = (int) $db->fetchOne("SELECT COUNT(*) FROM xf_report WHERE report_state = 'assigned'");
        return $this->success([
            'pending_approval' => $pending,
            'reports_open'     => $reportsOpen,
            'reports_assigned' => $reportsAssigned,
            'reports_active'   => $reportsOpen + $reportsAssigned,
        ]);
    }

    public function execute_getModerationHistory($params)
    {
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));
        $finder = \XF::finder('XF:ModeratorLog')->order('log_date', 'DESC')->limit($limit);
        $out = [];
        foreach ($finder->fetch() as $log) {
            $out[] = [
                'moderator_log_id' => (int)    $log->moderator_log_id,
                'user_id'          => (int)    $log->user_id,
                'username'         => (string) $log->username,
                'log_date'         => (int)    $log->log_date,
                'content_type'     => (string) $log->content_type,
                'content_id'       => (int)    $log->content_id,
                'action'           => (string) $log->action,
                'content_title'    => (string) $log->content_title,
            ];
        }
        return $this->success(['count' => count($out), 'history' => $out]);
    }

    public function execute_getReportedContent($params)
    {
        $report = \XF::em()->find('XF:Report', (int) $params['report_id']);
        if (!$report) {
            return $this->error('not_found', 'Report not found');
        }
        $content = $report->Content;
        return $this->success([
            'report_id'    => (int)    $report->report_id,
            'content_type' => (string) $report->content_type,
            'content_id'   => (int)    $report->content_id,
            'content_user_id' => (int) $report->content_user_id,
            'title'        => $content ? (string) ($content->getContentTitle('report') ?? '') : '',
            'available'    => (bool) $content,
        ]);
    }

    public function execute_getReportComments($params)
    {
        $report = \XF::em()->find('XF:Report', (int) $params['report_id']);
        if (!$report) {
            return $this->error('not_found', 'Report not found');
        }
        $comments = [];
        foreach ($report->Comments as $c) {
            $comments[] = [
                'report_comment_id' => (int)    $c->report_comment_id,
                'user_id'           => (int)    $c->user_id,
                'username'          => (string) $c->username,
                'comment_date'      => (int)    $c->comment_date,
                'message'           => (string) $c->message,
                'state_change'      => (string) $c->state_change,
            ];
        }
        return $this->success(['report_id' => (int) $report->report_id, 'count' => count($comments), 'comments' => $comments]);
    }

    public function execute_getSpamPatterns($params)
    {
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));
        $finder = \XF::finder('XF:SpamCleanerLog')->order('log_date', 'DESC')->limit($limit);
        $out = [];
        foreach ($finder->fetch() as $log) {
            $out[] = [
                'spam_cleaner_log_id' => (int)    $log->spam_cleaner_log_id,
                'user_id'             => (int)    $log->user_id,
                'username'            => (string) $log->username,
                'applied_by_user_id'  => (int)    $log->applied_user_id,
                'log_date'            => (int)    $log->log_date,
            ];
        }
        return $this->success(['count' => count($out), 'spam_events' => $out]);
    }

    // ── write / delete handlers (moderator rights re-checked) ──────────────────

    public function execute_assignModerator($params)
    {
        $visitor = \XF::visitor();
        $report = \XF::em()->find('XF:Report', (int) $params['report_id']);
        if (!$report) {
            return $this->error('not_found', 'Report not found');
        }
        if (!$report->canView() || !$report->canUpdate($err)) {
            return $this->error('no_permission', 'You do not have permission to manage this report');
        }
        $assignTo = \XF::em()->find('XF:User', (int) $params['assign_user_id']);
        if (!$assignTo) {
            return $this->error('not_found', 'Assignee user not found');
        }
        /** @var \XF\Service\Report\Commenter $commenter */
        $commenter = \XF::service('XF:Report\Commenter', $report);
        $commenter->setReportState('assigned', $assignTo);
        if (!$commenter->validate($errors)) {
            return $this->error('validation', implode('; ', $errors));
        }
        $commenter->save();
        return $this->success([
            'report_id'        => (int) $report->report_id,
            'assigned_user_id' => (int) $assignTo->user_id,
            'assigned_username' => (string) $assignTo->username,
            'report_state'     => (string) $report->report_state,
        ]);
    }

    public function execute_deleteSpam($params)
    {
        $type = (string) $params['content_type'];
        $id   = (int) $params['content_id'];
        $reason = trim((string) ($params['reason'] ?? '')) ?: 'Spam';

        if (!in_array($type, ['post', 'thread'], true)) {
            return $this->error('invalid_input', 'content_type must be "post" or "thread"');
        }
        $entityShort = $type === 'post' ? 'XF:Post' : 'XF:Thread';
        $entity = \XF::em()->find($entityShort, $id);
        if (!$entity) {
            return $this->error('not_found', ucfirst($type) . ' not found');
        }
        if (!$entity->canDelete('soft', $err)) {
            return $this->error('no_permission', $err ?: 'You do not have permission to delete this content');
        }
        /** @var \XF\Service\ThreadOrPost $deleter */
        $svcName = $type === 'post' ? 'XF:Post\Deleter' : 'XF:Thread\Deleter';
        $deleter = \XF::service($svcName, $entity);
        $deleter->delete('soft', $reason);
        return $this->success([
            'content_type' => $type,
            'content_id'   => $id,
            'deleted'      => true,
            'reason'       => $reason,
        ]);
    }
}
