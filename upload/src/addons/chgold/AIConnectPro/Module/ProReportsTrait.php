<?php

namespace chgold\AIConnectPro\Module;

/**
 * Reports bundle: 5 tools — list/get/resolve/reject/reply.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProReportsTrait
{
    protected function registerReportsTools()
    {
        $this->registerTool('listReports', [
            'description' => 'List reports in the moderation queue by state (open/assigned/resolved/rejected).',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'state' => ['type' => 'string', 'description' => 'open/assigned/resolved/rejected — default "open,assigned"'],
                    'limit' => ['type' => 'integer', 'description' => 'Max results (default 20, max 100)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getReport', [
            'description' => 'Get full details of a specific report + its comment thread.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['report_id'],
                'properties' => [
                    'report_id' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('resolveReport', [
            'description' => 'Mark a report as resolved with optional comment.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['report_id'],
                'properties' => [
                    'report_id' => ['type' => 'integer'],
                    'comment' => ['type' => 'string', 'description' => 'Optional resolution comment'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('rejectReport', [
            'description' => 'Reject a report (mark as rejected) with optional reason.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['report_id'],
                'properties' => [
                    'report_id' => ['type' => 'integer'],
                    'reason' => ['type' => 'string', 'description' => 'Optional rejection reason'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('replyToReport', [
            'description' => 'Add a moderator comment to a report thread without resolving.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['report_id', 'message'],
                'properties' => [
                    'report_id' => ['type' => 'integer'],
                    'message' => ['type' => 'string'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_listReports($params)
    {
        $state = (string) ($params['state'] ?? 'open,assigned');
        $states = array_map('trim', explode(',', $state));
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));

        /** @var \XF\Repository\ReportRepository $repo */
        $repo = \XF::em()->getRepository('XF:Report');
        $finder = $repo->findReports($states)->limit($limit);

        $out = [];
        foreach ($finder->fetch() as $r) {
            $out[] = [
                'report_id'      => (int)    $r->report_id,
                'content_type'   => (string) $r->content_type,
                'content_id'     => (int)    $r->content_id,
                'content_user_id' => (int)    $r->content_user_id,
                'content_info'   => (string) $r->content_info['title'] ?? '',
                'report_state'   => (string) $r->report_state,
                'assigner_user_id' => (int)  $r->assigner_user_id,
                'assigned_user_id' => (int)  $r->assigned_user_id,
                'comment_count'  => (int)    $r->comment_count,
                'first_report_date' => (int) $r->first_report_date,
                'last_modified_date' => (int) $r->last_modified_date,
            ];
        }
        return $this->success(['count' => count($out), 'reports' => $out]);
    }

    public function execute_getReport($params)
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
                'is_report'         => (bool)   $c->is_report,
            ];
        }

        return $this->success([
            'report_id'      => (int)    $report->report_id,
            'content_type'   => (string) $report->content_type,
            'content_id'     => (int)    $report->content_id,
            'content_user_id' => (int)    $report->content_user_id,
            'report_state'   => (string) $report->report_state,
            'assigned_user_id' => (int)  $report->assigned_user_id,
            'comment_count'  => (int)    $report->comment_count,
            'comments'       => $comments,
        ]);
    }

    public function execute_resolveReport($params)
    {
        return $this->changeReportState((int) $params['report_id'], 'resolved', $params['comment'] ?? '');
    }

    public function execute_rejectReport($params)
    {
        return $this->changeReportState((int) $params['report_id'], 'rejected', $params['reason'] ?? '');
    }

    public function execute_replyToReport($params)
    {
        // v1.2.4 security (CHECK_XF_002): was missing write scope + can*() checks.
        // XF native ReportController gates on $visitor->is_moderator.
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('write')) {
            return $this->error('insufficient_scope', 'The "write" scope is required for this operation');
        }
        if (!\XF::visitor()->is_moderator && !\XF::visitor()->is_admin) {
            return $this->error('no_permission', 'Report management requires moderator or admin');
        }

        $report = \XF::em()->find('XF:Report', (int) $params['report_id']);
        if (!$report) {
            return $this->error('not_found', 'Report not found');
        }
        if (!$report->canView()) {
            return $this->error('no_permission', 'You do not have permission to view/reply to this report');
        }

        /** @var \XF\Service\Report\CommenterService $svc */
        $svc = \XF::service('XF:Report\Commenter', $report, \XF::visitor());
        $svc->setMessage((string) $params['message']);
        $svc->save();

        return $this->success([
            'report_id' => $report->report_id,
            'replied'   => true,
        ]);
    }

    private function changeReportState(int $reportId, string $newState, string $comment): array
    {
        // v1.2.4 security: this helper is called by both execute_resolveReport
        // and execute_rejectReport (which have no local guards). Enforce here so
        // both callers inherit — write scope + moderator/admin permission +
        // per-report canView.
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('write')) {
            return $this->error('insufficient_scope', 'The "write" scope is required for this operation');
        }
        if (!\XF::visitor()->is_moderator && !\XF::visitor()->is_admin) {
            return $this->error('no_permission', 'Report management requires moderator or admin');
        }

        $report = \XF::em()->find('XF:Report', $reportId);
        if (!$report) {
            return $this->error('not_found', 'Report not found');
        }
        if (!$report->canView()) {
            return $this->error('no_permission', 'You do not have permission to manage this report');
        }

        /** @var \XF\Service\Report\CommenterService $svc */
        $svc = \XF::service('XF:Report\Commenter', $report, \XF::visitor());
        $svc->setReportState($newState);
        if ($comment !== '') {
            $svc->setMessage($comment);
        }
        $svc->save();

        return $this->success([
            'report_id' => $report->report_id,
            'state'     => $newState,
        ]);
    }
}
