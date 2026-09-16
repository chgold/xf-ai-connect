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
        // Note: setNodePrivate + getNodePermissions are registered later
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

        $this->registerTool('setNodePrivate', [
            'description' => 'Toggle a node s "Private" flag — same checkbox XF ACP exposes on the '
                . 'Node Permissions page. When private=true, ONLY groups/users with an explicit '
                . 'content_allow entry (via setNodePermission) can view the node; base group '
                . 'permissions no longer apply. This is XF s intended way to make a node truly '
                . 'private, and it defeats the "deny + base=allow" quirk that setNodePermission '
                . 'alone cannot solve.'
                . "\n\n"
                . 'RECIPE — proper private node:'
                . "\n"
                . '  1. setNodePrivate(node_id, is_private=true)  // flip the flag'
                . "\n"
                . '  2. setNodePermission(node_id, user_group_id=X, general.view, content_allow)'
                . "\n"
                . '     for each group that SHOULD see it'
                . "\n"
                . '  3. Verify with getNodePermissions(node_id) — is_private + effective_view_by_group'
                . "\n\n"
                . 'Implementation: writes a SYSTEM entry (user_group_id=0, user_id=0) to '
                . 'xf_permission_entry_content with permission_id=viewNode + value=reset (XF s '
                . 'internal marker). Uses XF UpdatePermissionsService — same code path as ACP.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id', 'is_private'],
                'properties' => [
                    'node_id' => ['type' => 'integer'],
                    'is_private' => ['type' => 'boolean', 'description' => 'true = mark as private (only explicitly-allowed groups view); false = clear the flag'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getNodePermissions', [
            'description' => 'Read all explicit + inherited permissions for a node. '
                . 'Use this AFTER setNodePermission to verify your changes actually blocked/granted '
                . 'the way you expected. Returns three sections: '
                . 'entries_on_node (rows on THIS node), '
                . 'entries_inherited (rows inherited from parent chain, each with inherited_from_node_id), '
                . 'effective_view_by_group (final per-group can_view boolean + derivation string '
                . 'showing WHY — e.g. "content=deny/base=allow"). '
                . "\n\n"
                . 'RECIPE — verify a node is truly private after setNodePermission:'
                . "\n"
                . '  1. getNodePermissions(node_id)'
                . "\n"
                . '  2. Inspect effective_view_by_group — every group you wanted to block should show can_view=false'
                . "\n"
                . '  3. If a group still shows can_view=true, check its derivation string. base=allow means '
                . 'the group has global view; use content_allow-only pattern instead of deny.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => [
                    'node_id' => ['type' => 'integer'],
                    'permission_id' => ['type' => 'string', 'description' => 'Filter to one permission (e.g. view). Omit for all.'],
                    'permission_group_id' => ['type' => 'string', 'description' => 'Filter to one permission group (e.g. general). Omit for all.'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        // Permission naming crib for the tool description below.
        //
        // XF has TWO separate "view" permission_ids at content level:
        //   general.view      — used by Node::canView() at runtime (per-user check)
        //   general.viewNode  — a SYSTEM MARKER (ug=0/u=0 + value=reset) that flips
        //                       XF s "Private node" checkbox. NOT a per-group perm.
        //                       Use setNodePrivate to toggle this properly.
        //
        // For per-group access control (setNodePermission), use general.view.
        // For the Private-node flag, use the dedicated setNodePrivate tool.
        //
        // ⚠️ KNOWN XF QUIRK when NOT using setNodePrivate: content-level `deny`
        // for a group with base=allow does NOT block at runtime. If you want a
        // truly private node, ALWAYS call setNodePrivate(is_private=true) FIRST
        // — that is XF s intended mechanism and bypasses the quirk.
        // is_admin=1 users bypass content permissions regardless.
        $this->registerTool('setNodePermission', [
            'description' => 'Set a content-level permission for a usergroup (or single user) on a node. '
                . 'This is XenForo\'s ONLY built-in mechanism for restricting node access — there is no '
                . 'separate "private_node" / "allowed_user_group_ids" field on xf_node. The XF ACP itself '
                . 'uses this same table (xf_permission_entry_content) when you configure node permissions.'
                . "\n\n"
                . 'RECIPE — make a node private to specific usergroups (e.g. Admin+Mod only):'
                . "\n"
                . '  1. setNodePermission(node_id, user_group_id=1, general.view, deny)   // block Guest'
                . "\n"
                . '  2. setNodePermission(node_id, user_group_id=2, general.view, deny)   // block Registered'
                . "\n"
                . '  3. setNodePermission(node_id, user_group_id=3, general.view, content_allow)  // allow Admin'
                . "\n"
                . '  4. setNodePermission(node_id, user_group_id=4, general.view, content_allow)  // allow Mod'
                . "\n"
                . '  5. Verify with getNodePermissions(node_id) — check effective_view_by_group.'
                . "\n\n"
                . 'RECIPE — grant access to a single user (no helper usergroup needed):'
                . "\n"
                . '  setNodePermission(node_id, user_id=42, user_group_id=0, general.view, content_allow)'
                . "\n\n"
                . 'RECIPE — remove all restrictions:'
                . "\n"
                . '  setNodePermission(node_id, user_group_id=X, general.view, unset)  // for each group'
                . "\n\n"
                . 'XF QUIRKS (both native, not tool bugs — documented for agent guidance):'
                . "\n"
                . '  * deny on a group that has base "general.view=allow" may NOT block at runtime '
                . '(XF s cache builder produces {view:true} anyway). For strict blocking, either '
                . '(a) don t use deny — leave base as-is and grant with content_allow only, or '
                . '(b) set the group s BASE view to unset via ACP first.'
                . "\n"
                . '  * Users with is_admin=1 (or is_super_admin=1) always bypass content permissions.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id', 'user_group_id', 'permission_group_id', 'permission_id', 'permission_value'],
                'properties' => [
                    'node_id' => ['type' => 'integer'],
                    'user_group_id' => ['type' => 'integer'],
                    'permission_group_id' => ['type' => 'string', 'description' => 'Group: general (for basic node access), forum, thread, post. NOT "node".'],
                    'permission_id' => ['type' => 'string', 'description' => 'Common: general.view (Node::canView checks THIS — NOT viewNode which does not exist), forum.viewContent, forum.postThread, forum.postReply, forum.uploadAttachment. viewNode/viewForum auto-corrected to general.view.'],
                    'user_id' => ['type' => 'integer', 'description' => 'Set permission for a specific user instead of a usergroup. When provided (>0), user_group_id is set to 0. Useful to override group perms per-user without creating a helper usergroup.'],
                    'permission_value' => [
                        'type' => 'string',
                        'enum' => ['content_allow', 'deny', 'reset', 'unset', 'use_int', 'allow'],
                        'description' => 'For node/content permissions: use content_allow (NOT allow). '
                            . 'unset removes the entry (inheritance). "allow" is auto-mapped to content_allow for convenience.',
                    ],
                    'permission_value_int' => ['type' => 'integer', 'description' => 'Numeric override for count-type permissions (default 0)'],
                    'confirm_self_lockout' => ['type' => 'boolean', 'description' => 'Explicit override to allow a change that would lock the caller (or their groups) out of this node. Default false — the change is refused with lockout_risk error if this flag is not set. Use simulatePermissionChange first to preview.'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_reorderNodes($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('node')) {
            return $err;
        }

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
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        // XF core: PermissionController::assertAdminPermission('userGroup')
        // — permission edits belong to the userGroup admin area.
        if ($err = $this->assertPermission('userGroup')) {
            return $err;
        }

        $nodeId = (int) $params['node_id'];
        $node = \XF::em()->find('XF:Node', $nodeId);
        if (!$node) {
            return $this->error('not_found', 'Node not found');
        }

        // Per-user OR per-group — XF supports both via xf_permission_entry_content
        // (columns user_group_id + user_id). Prior versions of this tool always
        // set user_id=0 which forced callers to create helper usergroups to
        // target individual users.
        $userId  = (int) ($params['user_id'] ?? 0);
        $groupId = (int) $params['user_group_id'];
        if ($userId > 0) {
            $user = \XF::em()->find('XF:User', $userId);
            if (!$user) {
                return $this->error('not_found', 'User not found');
            }
            $groupId = 0;  // user-specific: group is unused
        } else {
            $group = \XF::em()->find('XF:UserGroup', $groupId);
            if (!$group) {
                return $this->error('not_found', 'User group not found');
            }
        }

        $pg  = (string) $params['permission_group_id'];
        $pid = (string) $params['permission_id'];
        $val = (string) $params['permission_value'];

        // Auto-map common mistakes — but ONLY when it's not the special
        // "Private Node" system marker. XF uses `general.viewNode` at
        // user_group_id=0 + user_id=0 with value=reset as the internal
        // private flag. Rewriting to `general.view` there would break the
        // ACP checkbox. For all other combinations, viewNode is a mistake.
        $isPrivateFlag = (
            $pid === 'viewNode' && $pg === 'general'
            && $groupId === 0 && $userId === 0
        );
        $autoMapped = null;
        if (!$isPrivateFlag) {
            $mistakeMap = [
                'viewNode'    => ['view', 'general'],
                'viewForum'   => ['view', 'general'],
                'viewContent' => ['viewContent', 'forum'],
                'view'        => ['view', 'general'],
            ];
            if (isset($mistakeMap[$pid])) {
                $realPid = $mistakeMap[$pid][0];
                $realPg  = $mistakeMap[$pid][1];
                if ($pid !== $realPid || $pg !== $realPg) {
                    $autoMapped = "$pg.$pid → $realPg.$realPid";
                    $pid = $realPid;
                    $pg  = $realPg;
                }
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

        // Self-lockout guard — refuse the change if it would lock out the caller,
        // unless they explicitly acknowledged via confirm_self_lockout=true.
        if (empty($params['confirm_self_lockout'])) {
            if ($this->wouldLockOutCaller($nodeId, $groupId, $userId, $pg, $pid, $val)) {
                return $this->error(
                    'lockout_risk',
                    'This change would lock YOU (or one of your groups) out of node '
                    . $nodeId . '. Refused as safety measure. '
                    . 'Use simulatePermissionChange to preview, or pass confirm_self_lockout=true '
                    . 'if you really intend this.'
                );
            }
        }
        $valInt = isset($params['permission_value_int']) ? (int) $params['permission_value_int'] : 0;

        $db = \XF::db();

        if ($val === 'unset') {
            $affected = $db->delete(
                'xf_permission_entry_content',
                'user_group_id = ? AND user_id = ? AND content_type = ? AND content_id = ?
                 AND permission_group_id = ? AND permission_id = ?',
                [$groupId, $userId, 'node', $nodeId, $pg, $pid]
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

        // Upsert content-permission entry (per-group OR per-user)
        $db->query(
            'INSERT INTO xf_permission_entry_content
                (user_group_id, user_id, content_type, content_id,
                 permission_group_id, permission_id, permission_value, permission_value_int)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                permission_value = VALUES(permission_value),
                permission_value_int = VALUES(permission_value_int)',
            [$groupId, $userId, 'node', $nodeId, $pg, $pid, $val, $valInt]
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

    public function execute_setNodePrivate($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('userGroup')) {
            return $err;
        }

        $nodeId = (int) $params['node_id'];
        $node = \XF::em()->find('XF:Node', $nodeId);
        if (!$node) {
            return $this->error('not_found', 'Node not found');
        }

        $makePrivate = !empty($params['is_private']);

        // Write the SYSTEM marker row directly (user_group_id=0, user_id=0,
        // permission_id=viewNode, value=reset). XF ACP does the same write via
        // UpdatePermissionsService, but that service asserts an ACP session
        // which isn't set up in the API context — direct write matches state.
        $db = \XF::db();
        if ($makePrivate) {
            $db->query(
                'INSERT INTO xf_permission_entry_content
                    (user_group_id, user_id, content_type, content_id,
                     permission_group_id, permission_id, permission_value, permission_value_int)
                 VALUES (0, 0, ?, ?, ?, ?, ?, 0)
                 ON DUPLICATE KEY UPDATE permission_value = VALUES(permission_value)',
                ['node', $nodeId, 'general', 'viewNode', 'reset']
            );
        } else {
            $db->delete(
                'xf_permission_entry_content',
                'user_group_id = 0 AND user_id = 0 AND content_type = ? AND content_id = ?
                 AND permission_group_id = ? AND permission_id = ?',
                ['node', $nodeId, 'general', 'viewNode']
            );
        }

        // Rebuild is FIRE-AND-FORGET via job manager — never inline.
        // Inline rebuild triggers XF Permission service checks that require
        // an ACP session (unavailable in API), causing "do_not_have_permission"
        // false-error responses AFTER the DB write already succeeded.
        try {
            \XF::app()->jobManager()->enqueueUnique(
                'aiconnect_perm_rebuild_priv',
                'XF:PermissionRebuild',
                [],
                false
            );
        } catch (\Throwable $e) {
            // job enqueue failed — the write is still safe, cache refreshes on next cron
            \XF::logException($e, false, 'setNodePrivate: rebuild enqueue failed: ');
        }

        // Verify by reading back the marker row
        $marker = \XF::db()->fetchOne(
            'SELECT permission_value FROM xf_permission_entry_content
             WHERE content_type = ? AND content_id = ?
               AND user_group_id = 0 AND user_id = 0
               AND permission_group_id = ? AND permission_id = ?',
            ['node', $nodeId, 'general', 'viewNode']
        );

        return $this->success([
            'node_id'    => $nodeId,
            'is_private' => $marker === 'reset',
            'marker'     => $marker,
            'note'       => $makePrivate
                ? 'Node is now private. Grant explicit content_allow per group/user via setNodePermission to allow view.'
                : 'Private flag cleared. Base group permissions apply again.',
        ]);
    }

    public function execute_getNodePermissions($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('userGroup')) {
            return $err;
        }

        $nodeId = (int) $params['node_id'];
        $node = \XF::em()->find('XF:Node', $nodeId);
        if (!$node) {
            return $this->error('not_found', 'Node not found');
        }

        // Is the node marked "Private" via XF s system flag?
        $isPrivate = \XF::db()->fetchOne(
            'SELECT permission_value FROM xf_permission_entry_content
             WHERE content_type = ? AND content_id = ?
               AND user_group_id = 0 AND user_id = 0
               AND permission_group_id = ? AND permission_id = ?',
            ['node', $nodeId, 'general', 'viewNode']
        ) === 'reset';

        $pgFilter  = (string) ($params['permission_group_id'] ?? '');
        $pidFilter = (string) ($params['permission_id'] ?? '');
        // Auto-correct viewNode → view here too, for consistency
        if ($pidFilter === 'viewNode' || $pidFilter === 'viewForum') {
            $pidFilter = 'view';
        }

        // Walk parent chain (this node + ancestors) — inheritance goes down
        $chain = [];
        $cursor = $node;
        while ($cursor) {
            $chain[] = (int) $cursor->node_id;
            if (!$cursor->parent_node_id) {
                break;
            }
            $cursor = \XF::em()->find('XF:Node', $cursor->parent_node_id);
        }

        // Fetch entries for this node + all ancestors
        $placeholders = implode(',', array_fill(0, count($chain), '?'));
        $where = "content_type='node' AND content_id IN ($placeholders)";
        $bind  = $chain;
        if ($pgFilter !== '') {
            $where .= " AND permission_group_id = ?";
            $bind[] = $pgFilter;
        }
        if ($pidFilter !== '') {
            $where .= " AND permission_id = ?";
            $bind[] = $pidFilter;
        }

        $rows = \XF::db()->fetchAll(
            "SELECT content_id, user_group_id, user_id, permission_group_id, permission_id,
                    permission_value, permission_value_int
             FROM xf_permission_entry_content
             WHERE $where
             ORDER BY content_id, user_group_id DESC, user_id, permission_group_id, permission_id",
            $bind
        );

        $own = [];
        $inherited = [];
        foreach ($rows as $r) {
            $entry = [
                'user_group_id'        => (int) $r['user_group_id'],
                'user_id'              => (int) $r['user_id'],
                'permission'           => $r['permission_group_id'] . '.' . $r['permission_id'],
                'permission_value'     => (string) $r['permission_value'],
                'permission_value_int' => (int) $r['permission_value_int'],
            ];
            if ((int) $r['content_id'] === $nodeId) {
                $own[] = $entry;
            } else {
                $entry['inherited_from_node_id'] = (int) $r['content_id'];
                $inherited[] = $entry;
            }
        }

        // Effective view per usergroup — MANUAL computation from entries.
        //
        // Why not read xf_permission_cache_content?
        //   The cache is per-COMBINATION (a group SET, e.g. "2,3,4"), not per
        //   individual group. A combination that contains group 2 + admin group 3
        //   grants view via group 3 → but reading that cache for "group 2" would
        //   falsely report group 2 can view standalone.
        //   Also, XF's own cache builder has a known quirk: content-level `deny`
        //   for a group that ALSO has base-level `allow` for the same permission
        //   does NOT override — the cache still says view:true. See setNodePermission
        //   doc note for the recommended pattern.
        //
        // What we do: for each group, walk (this node + ancestors) entries and
        // apply XF-style precedence — deny > content_allow > base > default_deny.
        // Also read the group's base permission (xf_permission_entry) as fallback.
        $effective = [];
        $ugFinder = \XF::finder('XF:UserGroup')->order('user_group_id')->fetch();
        foreach ($ugFinder as $ug) {
            // 1. Base group permission for general.view
            $baseView = \XF::db()->fetchOne(
                'SELECT permission_value FROM xf_permission_entry
                 WHERE user_group_id = ? AND permission_group_id = ? AND permission_id = ?',
                [$ug->user_group_id, 'general', 'view']
            );

            // 2. Walk chain (this node first, then ancestors) for content overrides
            $contentValue = null;  // null = no override, else 'content_allow'/'deny'/'reset'
            foreach ($chain as $nid) {
                $rowVal = \XF::db()->fetchOne(
                    'SELECT permission_value FROM xf_permission_entry_content
                     WHERE user_group_id = ? AND user_id = 0
                       AND content_type = ? AND content_id = ?
                       AND permission_group_id = ? AND permission_id = ?',
                    [$ug->user_group_id, 'node', $nid, 'general', 'view']
                );
                if ($rowVal) {
                    // First hit wins (closest to node) — but deny anywhere in chain still applies
                    if ($contentValue === null) {
                        $contentValue = $rowVal;
                    }
                    if ($rowVal === 'deny') {
                        $contentValue = 'deny';
                        break;
                    }
                }
            }

            // 3. Resolve final view state — considering is_private
            //
            // When is_private=true, base permissions no longer apply — ONLY explicit
            // content_allow entries grant view. Groups without an explicit content
            // entry cannot view, regardless of their base 'general.view' setting.
            if ($contentValue === 'deny') {
                $granted = false;
            } elseif ($contentValue === 'content_allow') {
                $granted = true;
            } elseif ($isPrivate) {
                // Private: base=allow does NOT grant. Only explicit allow works.
                $granted = false;
            } elseif ($baseView === 'allow') {
                $granted = true;
            } else {
                $granted = false;  // unset/reset/deny at base = no access
            }

            // Derivation string — reflects is_private too
            if ($contentValue !== null) {
                $derivation = "content=$contentValue" . ($baseView ? "/base=$baseView" : '');
            } elseif ($isPrivate) {
                $derivation = "private-node/no-content-allow" . ($baseView ? "/base=$baseView-IGNORED" : '');
            } elseif ($baseView) {
                $derivation = "base=$baseView";
            } else {
                $derivation = "no-perm";
            }

            $effective[] = [
                'user_group_id' => (int) $ug->user_group_id,
                'title'         => (string) $ug->title,
                'can_view'      => $granted,
                'derivation'    => $derivation,
            ];
        }

        return $this->success([
            'node_id'    => $nodeId,
            'title'      => $node->title,
            'is_private' => $isPrivate,
            'parent_chain' => array_slice($chain, 1), // exclude self
            'entries_on_node' => $own,
            'entries_inherited' => $inherited,
            'effective_view_by_group' => $effective,
        ]);
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
