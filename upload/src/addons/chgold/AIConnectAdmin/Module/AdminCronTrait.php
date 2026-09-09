<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Admin — Cron bundle: list + manually trigger cron entries.
 *
 * 2 tools. Both require admin scope + is_admin + XF cron admin permission.
 * Manual trigger runs the cron entry synchronously (like the ACP "Run now" button).
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminCronTrait
{
    protected function registerCronTools()
    {
        $this->registerTool('listCronTasks', [
            'description' => 'List all cron entries (id, description, next_run, active). No arguments.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('triggerCronTask', [
            'description' => 'Manually run a cron entry synchronously (equivalent to ACP "Run now"). '
                . 'Returns completion status.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['entry_id'],
                'properties' => [
                    'entry_id' => ['type' => 'string', 'description' => 'Cron entry_id (e.g. "cleanUpSessions", from listCronTasks)'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_listCronTasks($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertCronPermission()) return $err;

        // findCronEntriesForList may not exist on older XF minor versions.
        // Use finder pattern directly.
        $entries = \XF::finder('XF:CronEntry')->order('entry_id')->fetch();
        $out = [];
        foreach ($entries as $e) {
            $out[] = [
                'entry_id' => (string) $e->entry_id,
                'description' => method_exists($e, 'getDescription')
                    ? (string) $e->getDescription()
                    : (string) $e->entry_id,
                'run_rules' => is_array($e->run_rules) ? $e->run_rules : [],
                'next_run' => (int) $e->next_run,
                'active' => (bool) $e->active,
                'addon_id' => (string) $e->addon_id,
            ];
        }
        return $this->success(['entries' => $out, 'count' => count($out)]);
    }

    public function execute_triggerCronTask($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertCronPermission()) return $err;

        $entryId = (string) $params['entry_id'];
        $entry = \XF::em()->find('XF:CronEntry', $entryId);
        if (!$entry) return $this->error('not_found', "Cron entry '$entryId' not found");

        // Guard against dead add-ons (same check XF ACP does in CronEntryController::actionRun)
        if ($entry->addon_id && (!$entry->AddOn || !$entry->AddOn->active)) {
            return $this->error(
                'invalid_state',
                "Cron entry '$entryId' belongs to inactive add-on '$entry->addon_id'"
            );
        }

        if (!$entry->hasCallback()) {
            return $this->error('invalid_state', "Cron entry '$entryId' has no callable callback");
        }

        // Exact pattern used by XF's ACP CronEntryController::actionRun (verified in XF source):
        //   call_user_func([$entry->cron_class, $entry->cron_method], $entry->toArray());
        call_user_func(
            [$entry->cron_class, $entry->cron_method],
            $entry->toArray()
        );

        // Reload to get updated next_run + last_run
        $entry = \XF::em()->find('XF:CronEntry', $entryId, ['forceRefresh' => true]);

        return $this->success([
            'entry_id' => $entryId,
            'ran' => true,
            'next_run' => (int) $entry->next_run,
            'active' => (bool) $entry->active,
        ]);
    }

    private function assertCronPermission(): ?array
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasAdminPermission('cron')) {
            return $this->error('no_permission', 'The "cron" admin permission is required');
        }
        return null;
    }
}
