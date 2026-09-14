<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Permission safety bundle (Group A): dry-run + self-lockout guard.
 * 1 tool: simulatePermissionChange.
 * Plus a shared helper wouldLockOutCaller() consumed by setNodePermission
 * when confirm_self_lockout is not set.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminPermissionsSafetyTrait
{
    protected function registerPermissionsSafetyTools()
    {
        $this->registerTool('simulatePermissionChange', [
            'description' => 'Dry-run a setNodePermission call — computes whether the change would '
                . 'lock out the caller or any admin group, WITHOUT applying it. Use before '
                . 'destructive permission changes to preview impact.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id', 'permission_group_id', 'permission_id', 'permission_value'],
                'properties' => [
                    'node_id' => ['type' => 'integer'],
                    'user_group_id' => ['type' => 'integer'],
                    'user_id' => ['type' => 'integer'],
                    'permission_group_id' => ['type' => 'string'],
                    'permission_id' => ['type' => 'string'],
                    'permission_value' => ['type' => 'string'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────

    public function execute_simulatePermissionChange($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('userGroup')) return $err;

        $nodeId  = (int)    $params['node_id'];
        $groupId = (int)    ($params['user_group_id'] ?? 0);
        $userId  = (int)    ($params['user_id'] ?? 0);
        $pg      = (string) $params['permission_group_id'];
        $pid     = (string) $params['permission_id'];
        $val     = (string) $params['permission_value'];

        // Auto-correct viewNode → view (except SYSTEM marker)
        $isPrivateFlag = ($pid === 'viewNode' && $pg === 'general' && $groupId === 0 && $userId === 0);
        if (!$isPrivateFlag && $pid === 'viewNode') { $pid = 'view'; $pg = 'general'; }

        $node = \XF::em()->find('XF:Node', $nodeId);
        if (!$node) return $this->error('not_found', "Node $nodeId not found");

        $callerId   = \XF::visitor()->user_id;
        // secondary_group_ids is already an array in XF entity (LIST_COMMA type)
        $secondary = (array) \XF::visitor()->secondary_group_ids;
        $callerGroups = array_map('intval', array_filter(array_merge(
            [\XF::visitor()->user_group_id],
            $secondary
        )));

        // Simulate: after the change, would groups {1,2,3,4, callerGroups...} still see the node?
        $checkGroups = array_unique(array_merge([1, 2, 3, 4], $callerGroups));
        $simulated = [];
        foreach ($checkGroups as $gid) {
            $simulated[$gid] = $this->simulateGroupView($nodeId, $gid, $groupId, $userId, $pg, $pid, $val);
        }

        $wouldLockOutCaller = false;
        foreach ($callerGroups as $gid) {
            if (isset($simulated[$gid]) && !$simulated[$gid]) {
                $wouldLockOutCaller = true;
                break;
            }
        }

        return $this->success([
            'node_id' => $nodeId,
            'proposed_change' => "$pg.$pid = $val for " . ($userId > 0 ? "user_id=$userId" : "user_group_id=$groupId"),
            'would_lock_out_caller' => $wouldLockOutCaller,
            'simulated_view_by_group' => $simulated,
            'note' => $wouldLockOutCaller
                ? 'THIS CHANGE WOULD LOCK YOU OUT of this node. Pass confirm_self_lockout=true '
                    . 'to setNodePermission to proceed anyway.'
                : 'Change is safe with respect to caller access.',
        ]);
    }

    /**
     * Simulate whether a group would have view on a node AFTER the proposed change.
     * Uses same precedence rules as getNodePermissions effective_view_by_group.
     */
    protected function simulateGroupView(
        int $nodeId,
        int $group,
        int $changeGroup,
        int $changeUser,
        string $changePg,
        string $changePid,
        string $changeVal
    ): bool {
        // Only relevant for general.view permission changes
        if ($changePg !== 'general' || $changePid !== 'view') {
            return true;  // change doesn't affect view
        }

        // Base group permission
        $baseView = \XF::db()->fetchOne(
            'SELECT permission_value FROM xf_permission_entry
             WHERE user_group_id = ? AND permission_group_id = ? AND permission_id = ?',
            [$group, 'general', 'view']
        );

        // Content permissions on this node + ancestors — apply the simulated change first
        $chain = $this->getNodeChain($nodeId);
        $contentValue = null;
        foreach ($chain as $nid) {
            // Check if the simulated change would apply here
            if ($nid === $nodeId && $changeGroup === $group && $changeUser === 0) {
                if ($contentValue === null) $contentValue = $changeVal;
                if ($changeVal === 'deny') { $contentValue = 'deny'; break; }
                continue;
            }
            $rowVal = \XF::db()->fetchOne(
                'SELECT permission_value FROM xf_permission_entry_content
                 WHERE user_group_id = ? AND user_id = 0
                   AND content_type = ? AND content_id = ?
                   AND permission_group_id = ? AND permission_id = ?',
                [$group, 'node', $nid, 'general', 'view']
            );
            if ($rowVal) {
                if ($contentValue === null) $contentValue = $rowVal;
                if ($rowVal === 'deny') { $contentValue = 'deny'; break; }
            }
        }

        if ($contentValue === 'deny')           return false;
        if ($contentValue === 'content_allow')  return true;
        if ($baseView === 'allow')              return true;
        return false;
    }

    protected function getNodeChain(int $nodeId): array
    {
        $chain = [];
        $cursor = \XF::em()->find('XF:Node', $nodeId);
        while ($cursor) {
            $chain[] = (int) $cursor->node_id;
            if (!$cursor->parent_node_id) break;
            $cursor = \XF::em()->find('XF:Node', $cursor->parent_node_id);
        }
        return $chain;
    }

    /**
     * Called by setNodePermission to check if a change would lock out the caller.
     * Returns true = would lock out.
     */
    public function wouldLockOutCaller(
        int $nodeId, int $changeGroup, int $changeUser,
        string $pg, string $pid, string $val
    ): bool {
        $secondary = (array) \XF::visitor()->secondary_group_ids;
        $callerGroups = array_map('intval', array_filter(array_merge(
            [\XF::visitor()->user_group_id],
            $secondary
        )));

        // Only check for view permission changes
        if ($pg !== 'general' || $pid !== 'view') return false;

        foreach ($callerGroups as $gid) {
            if (!$this->simulateGroupView($nodeId, $gid, $changeGroup, $changeUser, $pg, $pid, $val)) {
                return true;
            }
        }
        return false;
    }
}
