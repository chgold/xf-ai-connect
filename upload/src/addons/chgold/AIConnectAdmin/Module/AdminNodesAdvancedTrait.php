<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Admin — Nodes advanced bundle: node display order + per-node permissions.
 *
 * Closes the gaps identified in the tool inventory (2026-09-09) for node
 * management beyond basic CRUD:
 *   * reorderNodes — bulk update display_order for siblings under a parent
 *   * setNodePermission — grant/deny a specific permission for a usergroup on a node
 *
 * Both require admin scope + is_admin. Uses xf_permission_entry_content for
 * per-node overrides (XF's content-permission model).
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminNodesAdvancedTrait
{
    protected function registerNodesAdvancedTools()
    {
        $this->registerTool('reorderNodes', [
            'description' => 'Bulk update display_order for a set of sibling nodes under the same parent. '
                . 'Pass an ordered array of node_ids; positions are assigned by array index (0,10,20,...) '
                . 'to leave room for future inserts.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_ids'],
                'properties' => [
                    'node_ids' => [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                        'description' => 'Ordered list of node IDs to reorder (must all share the same parent_node_id)',
                        'minItems' => 1,
                    ],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('setNodePermission', [
            'description' => 'Grant or deny a permission for a usergroup on a specific node. '
                . 'XF content-permission model: content_type=node, content_id=node_id. '
                . 'permission_value=unset removes the entry (falls back to inherited).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id', 'user_group_id', 'permission_group_id', 'permission_id', 'permission_value'],
                'properties' => [
                    'node_id' => ['type' => 'integer'],
                    'user_group_id' => ['type' => 'integer'],
                    'permission_group_id' => ['type' => 'string', 'description' => 'e.g. forum, thread, post'],
                    'permission_id' => ['type' => 'string', 'description' => 'e.g. postThread, viewOthers, viewAny'],
                    'permission_value' => [
                        'type' => 'string',
                        'enum' => ['allow', 'deny', 'reset', 'content_allow', 'unset'],
                        'description' => 'unset removes the entry (inheritance)',
                    ],
                    'permission_value_int' => ['type' => 'integer', 'description' => 'Numeric override for count-type permissions (default 0)'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_reorderNodes($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('node')) return $err;

        $ids = array_values(array_filter(array_map('intval', (array) $params['node_ids'])));
        if (empty($ids)) {
            return $this->error('validation_failed', 'node_ids must be a non-empty array of integers');
        }

        $nodes = \XF::em()->findByIds('XF:Node', $ids);
        if (count($nodes) !== count($ids)) {
            $missing = array_diff($ids, array_keys($nodes->toArray()));
            return $this->error('not_found', 'Nodes not found: ' . implode(',', $missing));
        }

        $parents = [];
        foreach ($nodes as $n) {
            $parents[$n->parent_node_id] = true;
        }
        if (count($parents) !== 1) {
            return $this->error(
                'validation_failed',
                'All nodes must share the same parent_node_id. Found: ' . implode(',', array_keys($parents))
            );
        }

        $order = 0;
        $assignments = [];
        foreach ($ids as $id) {
            $node = $nodes[$id];
            $node->display_order = $order;
            $node->save();
            $assignments[$id] = $order;
            $order += 10;
        }

        return $this->success([
            'parent_node_id' => (int) array_key_first($parents),
            'reordered' => count($ids),
            'assignments' => $assignments,
        ]);
    }

    public function execute_setNodePermission($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        // XF core: PermissionController::assertAdminPermission('userGroup')
        // — permission edits belong to the userGroup admin area.
        if ($err = $this->assertPermission('userGroup')) return $err;

        $nodeId = (int) $params['node_id'];
        $node = \XF::em()->find('XF:Node', $nodeId);
        if (!$node) return $this->error('not_found', 'Node not found');

        $groupId = (int) $params['user_group_id'];
        $group = \XF::em()->find('XF:UserGroup', $groupId);
        if (!$group) return $this->error('not_found', 'User group not found');

        $pg  = (string) $params['permission_group_id'];
        $pid = (string) $params['permission_id'];
        $val = (string) $params['permission_value'];
        $valInt = isset($params['permission_value_int']) ? (int) $params['permission_value_int'] : 0;

        $db = \XF::db();

        if ($val === 'unset') {
            $affected = $db->delete(
                'xf_permission_entry_content',
                'user_group_id = ? AND user_id = 0 AND content_type = ? AND content_id = ?
                 AND permission_group_id = ? AND permission_id = ?',
                [$groupId, 'node', $nodeId, $pg, $pid]
            );
            $this->enqueuePermissionRebuild();
            return $this->success([
                'node_id' => $nodeId,
                'user_group_id' => $groupId,
                'permission' => "$pg.$pid",
                'unset' => true,
                'affected' => $affected,
            ]);
        }

        if (!in_array($val, ['allow', 'deny', 'reset', 'content_allow'], true)) {
            return $this->error(
                'validation_failed',
                'permission_value must be allow/deny/reset/content_allow/unset'
            );
        }

        // Upsert content-permission entry
        $db->query(
            'INSERT INTO xf_permission_entry_content
                (user_group_id, user_id, content_type, content_id,
                 permission_group_id, permission_id, permission_value, permission_value_int)
             VALUES (?, 0, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                permission_value = VALUES(permission_value),
                permission_value_int = VALUES(permission_value_int)',
            [$groupId, 'node', $nodeId, $pg, $pid, $val, $valInt]
        );
        $this->enqueuePermissionRebuild();

        return $this->success([
            'node_id' => $nodeId,
            'user_group_id' => $groupId,
            'permission' => "$pg.$pid",
            'permission_value' => $val,
            'permission_value_int' => $valInt,
        ]);
    }

    private function enqueuePermissionRebuild(): void
    {
        \XF::app()->jobManager()->enqueueUnique(
            'aiconnect_perm_rebuild_' . uniqid(),
            'XF:PermissionRebuild',
            [],
            false
        );
    }
}
