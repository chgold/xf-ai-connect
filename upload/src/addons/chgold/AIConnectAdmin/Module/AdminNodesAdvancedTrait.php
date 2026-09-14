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

        // Permission naming crib for the tool description below.
        // XF Node canView() → hasContentPermission('node', id, 'view') — so use
        // general.view (NOT viewNode). Same for other common actions:
        //   general.view          — see the node at all
        //   forum.viewContent     — read threads/posts inside a Forum node
        //   forum.postThread      — create threads
        //   forum.postReply       — reply
        //   forum.uploadAttachment — attach files
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
                    'permission_group_id' => ['type' => 'string', 'description' => 'Group: general (for basic node access), forum, thread, post. NOT "node".'],
                    'permission_id' => ['type' => 'string', 'description' => 'Common: general.view (Node::canView checks THIS — NOT viewNode which does not exist), forum.viewContent, forum.postThread, forum.postReply, forum.uploadAttachment. viewNode/viewForum auto-corrected to general.view.'],
                    'permission_value' => [
                        'type' => 'string',
                        'enum' => ['content_allow', 'deny', 'reset', 'unset', 'use_int', 'allow'],
                        'description' => 'For node/content permissions: use content_allow (NOT allow). '
                            . 'unset removes the entry (inheritance). "allow" is auto-mapped to content_allow for convenience.',
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

        // Auto-map common mistakes to the actual XF permission_id.
        // XF's Node::canView() calls hasContentPermission('node', id, 'view')
        // — NOT 'viewNode' (which doesn't exist in xf_permission at all).
        // Agents keep guessing wrong names; auto-correct + warn in response.
        $mistakeMap = [
            'viewNode'    => ['view', 'general'],
            'viewForum'   => ['view', 'general'],
            'viewContent' => ['viewContent', 'forum'],
            'view'        => ['view', 'general'],  // idempotent for correct callers
        ];
        $autoMapped = null;
        if (isset($mistakeMap[$pid])) {
            $realPid = $mistakeMap[$pid][0];
            $realPg  = $mistakeMap[$pid][1];
            if ($pid !== $realPid || $pg !== $realPg) {
                $autoMapped = "$pg.$pid → $realPg.$realPid";
                $pid = $realPid;
                $pg  = $realPg;
            }
        }
        // Verify the permission actually exists (helps agents catch typos)
        $permExists = \XF::db()->fetchOne(
            'SELECT permission_id FROM xf_permission WHERE permission_group_id = ? AND permission_id = ?',
            [$pg, $pid]
        );
        if (!$permExists) {
            return $this->error(
                'unknown_permission',
                "Permission '$pg.$pid' does not exist in xf_permission. "
                . "For node view use general.view. For posting use forum.postThread/postReply. "
                . "Query xf_permission to see valid IDs."
            );
        }
        $valInt = isset($params['permission_value_int']) ? (int) $params['permission_value_int'] : 0;

        $db = \XF::db();

        if ($val === 'unset') {
            $affected = $db->delete(
                'xf_permission_entry_content',
                'user_group_id = ? AND user_id = 0 AND content_type = ? AND content_id = ?
                 AND permission_group_id = ? AND permission_id = ?',
                [$groupId, 'node', $nodeId, $pg, $pid]
            );
            $this->enqueuePermissionRebuild('node', $nodeId);
            return $this->success([
                'node_id' => $nodeId,
                'user_group_id' => $groupId,
                'permission' => "$pg.$pid",
                'unset' => true,
                'affected' => $affected,
            ]);
        }

        // xf_permission_entry_content enum accepts: unset, reset, content_allow, deny, use_int
        // (NOT 'allow' — that's for xf_permission_entry global permissions only).
        // Auto-map 'allow' → 'content_allow' for backward-compatible callers.
        if ($val === 'allow') {
            $val = 'content_allow';
        }
        if (!in_array($val, ['content_allow', 'deny', 'reset', 'use_int'], true)) {
            return $this->error(
                'validation_failed',
                'permission_value for content permissions must be content_allow/deny/reset/use_int/unset '
                . '(NOT "allow" — that is for global permissions only; use "content_allow" instead)'
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
        $this->enqueuePermissionRebuild('node', $nodeId);

        // Verify the permission actually took effect (round-trip check)
        \XF::em()->clearEntityCache('XF:Node', $nodeId);
        $verifyNode = \XF::em()->find('XF:Node', $nodeId);
        $canViewNow = $verifyNode ? $verifyNode->canView() : null;

        $out = [
            'node_id' => $nodeId,
            'user_group_id' => $groupId,
            'permission' => "$pg.$pid",
            'permission_value' => $val,
            'permission_value_int' => $valInt,
            'canView_visitor_after' => $canViewNow,
        ];
        if ($autoMapped !== null) {
            $out['auto_mapped'] = $autoMapped;
            $out['note'] = "Requested permission was auto-corrected. XF's Node canView() "
                . "actually checks 'general.view' (not 'viewNode'/'viewForum'). Use general.view "
                . "next time to avoid the auto-correct.";
        }
        return $out === $out ? $this->success($out) : $out;  // preserve API shape
    }

    /**
     * Rebuild permission caches IMMEDIATELY so a subsequent getNode/listNodes
     * call in the same session reflects the change.
     *
     * XF has TWO caches to consider:
     *   1. xf_permission_combination — the base group permissions
     *   2. xf_permission_cache_content — per (combination, content_type, content_id)
     *      → PermissionSet::hasContentPermission reads from THIS one
     *
     * Rebuilding only #1 was the previous bug: setNodePermission wrote the
     * entry but the visitor still saw canView=false because the content
     * cache #2 was stale.
     *
     * Now we call analyzeCombinationContent for every combination on the
     * specific (content_type, content_id) that was just changed.
     */
    private function enqueuePermissionRebuild(string $contentType = null, int $contentId = null): void
    {
        try {
            $builder = \XF::app()->permissionBuilder();
            $combos = \XF::em()->getFinder('XF:PermissionCombination')->fetch();

            foreach ($combos as $combo) {
                // Rebuild base combination (covers non-content permissions)
                $builder->rebuildCombination($combo);
                // Rebuild content cache for the specific node just modified
                if ($contentType !== null && $contentId !== null) {
                    $builder->analyzeCombinationContent($combo, $contentType, $contentId);
                }
            }
        } catch (\Throwable $e) {
            // Fallback: enqueue for the job runner
            \XF::app()->jobManager()->enqueueUnique(
                'aiconnect_perm_rebuild',
                'XF:PermissionRebuild',
                [],
                false
            );
        }
    }
}
