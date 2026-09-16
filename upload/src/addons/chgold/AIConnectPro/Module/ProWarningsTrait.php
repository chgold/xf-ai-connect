<?php

namespace chgold\AIConnectPro\Module;

/**
 * Warnings bundle: issue/delete/list warnings + list warning definitions.
 * 4 tools. XF API: WarnService for issue, Warning entity for delete/list.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProWarningsTrait
{
    protected function registerWarningsTools()
    {
        $this->registerTool('issueWarning', [
            'description' => 'Issue a warning to a user for a post/thread/profile-post. '
                . 'Either warning_definition_id (predefined) OR title+points+expiry_days (custom).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id', 'content_type', 'content_id'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'User to warn'],
                    'content_type' => ['type' => 'string', 'description' => 'post / thread / profile_post'],
                    'content_id' => ['type' => 'integer', 'description' => 'ID of the offending content'],
                    'warning_definition_id' => ['type' => 'integer', 'description' => 'Use a predefined warning (from listWarningDefinitions)'],
                    'title' => ['type' => 'string', 'description' => 'Custom warning title (if no definition_id)'],
                    'points' => ['type' => 'integer', 'description' => 'Custom warning points (if no definition_id)'],
                    'expiry_days' => ['type' => 'integer', 'description' => 'Custom expiry in days (0=never)'],
                    'notes' => ['type' => 'string', 'description' => 'Private moderator notes'],
                    'send_conversation' => ['type' => 'boolean', 'description' => 'Send PM to user with warning explanation'],
                    'conversation_message' => ['type' => 'string', 'description' => 'PM body (if send_conversation)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('deleteWarning', [
            'description' => 'Delete/revoke an existing warning by warning_id. Reverses points.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['warning_id'],
                'properties' => [
                    'warning_id' => ['type' => 'integer'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('listWarnings', [
            'description' => 'List warnings for a user, or all recent warnings if user_id omitted.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'Filter by user (optional)'],
                    'active_only' => ['type' => 'boolean', 'description' => 'Only non-expired warnings (default false)'],
                    'limit' => ['type' => 'integer', 'description' => 'Max results (default 20, max 100)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('listWarningDefinitions', [
            'description' => 'List all predefined warning definitions (title, points, default expiry).',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);
    }

    // ── execute methods ──────────────────────────────────────────────────

    public function execute_issueWarning($params)
    {
        // v1.2.4 security (CHECK_XF_002): was missing write scope + warn permission.
        // XF native uses hasPermission('general', 'warn') + $user->canWarn() +
        // per-content canWarn (on Post/Thread/ProfilePost).
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('write')) {
            return $this->error('insufficient_scope', 'The "write" scope is required for this operation');
        }
        $visitor = \XF::visitor();
        if (!$visitor->user_id) return $this->error('no_permission', 'Must be authenticated');
        if (!$visitor->hasPermission('general', 'warn')) {
            return $this->error('no_permission', 'The "warn" permission is required to issue warnings');
        }

        $user = \XF::em()->find('XF:User', (int) $params['user_id']);
        if (!$user) return $this->error('not_found', 'User not found');
        if (!$user->canWarn($error)) {
            return $this->error('no_permission', $error ?: 'This user cannot be warned by you');
        }

        $contentType = (string) $params['content_type'];
        $contentId   = (int)    $params['content_id'];
        $shortName   = $this->contentTypeToShortName($contentType);
        if (!$shortName) return $this->error('invalid_param', "Unknown content_type: $contentType");

        $content = \XF::em()->find($shortName, $contentId);
        if (!$content) return $this->error('not_found', "$contentType $contentId not found");
        if (method_exists($content, 'canWarn') && !$content->canWarn($cErr)) {
            return $this->error('no_permission', $cErr ?: "You cannot warn on this $contentType");
        }

        // WarnService::__construct(App, User $user, $contentType, Entity $content, User $warningBy)
        // XF::service() auto-injects App, so we pass: user, contentType, content, warningBy
        /** @var \XF\Service\User\WarnService $svc */
        $svc = \XF::service('XF:User\Warn', $user, $contentType, $content, $visitor);

        if (!empty($params['warning_definition_id'])) {
            $def = \XF::em()->find('XF:WarningDefinition', (int) $params['warning_definition_id']);
            if (!$def) return $this->error('not_found', 'Warning definition not found');
            $svc->setFromDefinition($def);
        } elseif (!empty($params['title']) && isset($params['points'])) {
            $expiry = !empty($params['expiry_days']) ? ['years' => 0, 'months' => 0, 'days' => (int)$params['expiry_days']] : null;
            $svc->setFromCustom(
                (string) $params['title'],
                (int)    $params['points'],
                $expiry
            );
        } else {
            return $this->error('invalid_param', 'Provide warning_definition_id OR title+points');
        }

        if (!empty($params['notes'])) $svc->setNotes((string) $params['notes']);
        if (!empty($params['send_conversation']) && !empty($params['conversation_message'])) {
            $svc->withConversation((string) ($params['title'] ?? 'Warning'), (string) $params['conversation_message']);
        }

        $warning = $svc->save();
        return $this->success([
            'warning_id'   => (int) $warning->warning_id,
            'user_id'      => (int) $user->user_id,
            'points'       => (int) $warning->points,
            'expiry_date'  => (int) $warning->expiry_date,
            'content_type' => $contentType,
            'content_id'   => $contentId,
        ]);
    }

    public function execute_deleteWarning($params)
    {
        // v1.2.4 security (CHECK_XF_002): was missing write scope + delete permission.
        // XF native Warning::canDelete uses hasPermission('general', 'manageWarning').
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('write')) {
            return $this->error('insufficient_scope', 'The "write" scope is required for this operation');
        }

        $warning = \XF::em()->find('XF:Warning', (int) $params['warning_id']);
        if (!$warning) return $this->error('not_found', 'Warning not found');
        if (method_exists($warning, 'canDelete') && !$warning->canDelete($error)) {
            return $this->error('no_permission', $error ?: 'You cannot delete this warning');
        }

        $userId = $warning->user_id;
        $points = $warning->points;
        $warning->delete();

        return $this->success([
            'warning_id'    => (int) $params['warning_id'],
            'deleted'       => true,
            'points_removed' => $points,
            'user_id'       => $userId,
        ]);
    }

    public function execute_listWarnings($params)
    {
        $limit  = min(100, max(1, (int) ($params['limit'] ?? 20)));
        $finder = \XF::finder('XF:Warning')->order('warning_date', 'DESC')->limit($limit);

        if (!empty($params['user_id'])) {
            $finder->where('user_id', (int) $params['user_id']);
        }
        if (!empty($params['active_only'])) {
            $finder->where('is_expired', 0);
        }

        $out = [];
        foreach ($finder->fetch() as $w) {
            $out[] = [
                'warning_id'    => (int)    $w->warning_id,
                'user_id'       => (int)    $w->user_id,
                'content_type'  => (string) $w->content_type,
                'content_id'    => (int)    $w->content_id,
                'content_title' => (string) $w->content_title,
                'title'         => (string) $w->title,
                'points'        => (int)    $w->points,
                'warning_date'  => (int)    $w->warning_date,
                'expiry_date'   => (int)    $w->expiry_date,
                'is_expired'    => (bool)   $w->is_expired,
                'notes'         => (string) $w->notes,
                'warning_user_id' => (int)  $w->warning_user_id,
            ];
        }
        return $this->success(['count' => count($out), 'warnings' => $out]);
    }

    public function execute_listWarningDefinitions($params)
    {
        $defs = \XF::finder('XF:WarningDefinition')->order('warning_definition_id')->fetch();
        $out = [];
        foreach ($defs as $d) {
            $out[] = [
                'warning_definition_id' => (int)    $d->warning_definition_id,
                'title'                 => (string) $d->title,
                'points_default'        => (int)    $d->points_default,
                'expiry_type'           => (string) $d->expiry_type,
                'expiry_default'        => (int)    $d->expiry_default,
                'is_expiry_forever'     => $d->expiry_type === 'never',
            ];
        }
        return $this->success(['count' => count($out), 'definitions' => $out]);
    }

    private function contentTypeToShortName(string $type): ?string
    {
        return [
            'post'         => 'XF:Post',
            'thread'       => 'XF:Thread',
            'profile_post' => 'XF:ProfilePost',
            'user'         => 'XF:User',
        ][$type] ?? null;
    }
}
