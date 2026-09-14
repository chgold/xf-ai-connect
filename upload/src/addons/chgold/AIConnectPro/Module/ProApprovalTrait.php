<?php

namespace chgold\AIConnectPro\Module;

/**
 * Approval queue bundle: 4 tools — list/approve/reject/deletion-log.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProApprovalTrait
{
    protected function registerApprovalTools()
    {
        $this->registerTool('listApprovalQueue', [
            'description' => 'List content awaiting moderator approval (unapproved posts/threads/profile posts).',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'content_type' => ['type' => 'string', 'description' => 'Filter by type (post/thread/profile_post) — optional'],
                    'limit' => ['type' => 'integer', 'description' => 'Max items (default 20, max 100)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('approveContent', [
            'description' => 'Approve queued content by content_type + content_id. Uses the type-specific approver service.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['content_type', 'content_id'],
                'properties' => [
                    'content_type' => ['type' => 'string', 'description' => 'post / thread / profile_post'],
                    'content_id' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('rejectContent', [
            'description' => 'Reject and soft-delete queued content.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['content_type', 'content_id'],
                'properties' => [
                    'content_type' => ['type' => 'string'],
                    'content_id' => ['type' => 'integer'],
                    'reason' => ['type' => 'string', 'description' => 'Rejection reason (optional)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('listDeletionLog', [
            'description' => 'List soft-deleted content (recoverable). Read-only.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'content_type' => ['type' => 'string', 'description' => 'Filter by content_type (optional)'],
                    'limit' => ['type' => 'integer', 'description' => 'Max items (default 20, max 100)'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_listApprovalQueue($params)
    {
        /** @var \XF\Repository\ApprovalQueueRepository $repo */
        $repo = \XF::em()->getRepository('XF:ApprovalQueue');
        $finder = $repo->findUnapprovedContent();

        if (!empty($params['content_type'])) {
            $finder->where('content_type', (string) $params['content_type']);
        }

        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));
        $finder->limit($limit);

        $items = $finder->fetch();
        $items = $repo->addContentToUnapprovedItems($items);

        $out = [];
        foreach ($items as $item) {
            $out[] = [
                'content_type'    => (string) $item->content_type,
                'content_id'      => (int)    $item->content_id,
                'content_date'    => (int)    $item->content_date,
                'content_user_id' => (int)    $item->content_user_id,
                'title'           => $item->Content->title ?? '',
            ];
        }
        return $this->success(['count' => count($out), 'items' => $out]);
    }

    public function execute_approveContent($params)
    {
        [$shortName, $svcName] = $this->contentTypeMap((string) $params['content_type']);
        if (!$shortName) return $this->error('invalid_param', 'Unknown content_type');

        $content = \XF::em()->find($shortName, (int) $params['content_id']);
        if (!$content) return $this->error('not_found', 'Content not found');

        $svc = \XF::service($svcName, $content);
        $svc->approve();

        return $this->success([
            'content_type' => $params['content_type'],
            'content_id'   => (int) $params['content_id'],
            'approved'     => true,
        ]);
    }

    public function execute_rejectContent($params)
    {
        [$shortName, ] = $this->contentTypeMap((string) $params['content_type']);
        if (!$shortName) return $this->error('invalid_param', 'Unknown content_type');

        $content = \XF::em()->find($shortName, (int) $params['content_id']);
        if (!$content) return $this->error('not_found', 'Content not found');

        // Post/Thread/ProfilePost deleters share the same soft-delete API
        $deleterSvc = str_replace(':Approver', ':Deleter', $this->contentTypeMap((string) $params['content_type'])[1]);
        $svc = \XF::service($deleterSvc, $content);
        $svc->setLogUrl(false);
        $svc->delete('soft', (string) ($params['reason'] ?? ''));

        return $this->success([
            'content_type' => $params['content_type'],
            'content_id'   => (int) $params['content_id'],
            'rejected'     => true,
        ]);
    }

    public function execute_listDeletionLog($params)
    {
        $finder = \XF::finder('XF:DeletionLog')->order('delete_date', 'DESC');
        if (!empty($params['content_type'])) {
            $finder->where('content_type', (string) $params['content_type']);
        }
        $limit = min(100, max(1, (int) ($params['limit'] ?? 20)));
        $finder->limit($limit);

        $out = [];
        foreach ($finder->fetch() as $l) {
            $out[] = [
                'content_type'    => (string) $l->content_type,
                'content_id'      => (int)    $l->content_id,
                'delete_date'     => (int)    $l->delete_date,
                'delete_user_id'  => (int)    $l->delete_user_id,
                'delete_reason'   => (string) $l->delete_reason,
            ];
        }
        return $this->success(['count' => count($out), 'items' => $out]);
    }

    private function contentTypeMap(string $type): array
    {
        return [
            'post'         => ['XF:Post', 'XF:Post\Approver'],
            'thread'       => ['XF:Thread', 'XF:Thread\Approver'],
            'profile_post' => ['XF:ProfilePost', 'XF:ProfilePost\Approver'],
        ][$type] ?? [null, null];
    }
}
