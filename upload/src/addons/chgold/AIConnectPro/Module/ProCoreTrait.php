<?php

namespace chgold\AIConnectPro\Module;

/**
 * Core utility tools bundled as "core". These are cross-cutting identity /
 * lookup helpers that almost every other bundle needs (Messaging needs to
 * resolve a recipient username, Moderation needs to know who the acting user
 * is, etc.), so gating them behind a specific feature bundle would create
 * hidden dependencies and confuse customers who buy a single bundle.
 *
 * ProModule always loads this trait when ANY Pro bundle is active — i.e.
 * whenever hasBundle('*') OR hasBundle('core') OR hasBundle(any_other) is true.
 */
trait ProCoreTrait
{
    protected function registerCoreTools()
    {
        // NOTE: there is deliberately no getMe here. It duplicated the free
        // add-on's getCurrentUser, which left agents with two tools for one job
        // and no way to tell them apart. The two extra fields getMe exposed
        // (user_group_id, is_super_admin) were folded into getCurrentUser, so
        // removing it costs nothing.
        $this->registerTool('findUserByName', [
            'description' => 'Find a user by (start of) username',
            'input_schema' => [
                'type' => 'object',
                'required' => ['username'],
                'properties' => ['username' => ['type' => 'string', 'description' => 'Username or start of username']],
            ],
        ]);
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- dynamic dispatch execute_<name>

    public function execute_findUserByName($params)
    {
        // Looking members up by name prefix is member-list access: without this
        // gate the tool enumerated the whole user base for any caller with a
        // read token, on a board that may deliberately hide its member list.
        // getUserProfile already enforces the same permission — this keeps the
        // two user-facing tools on one standard.
        if (!\XF::visitor()->canViewMemberList()) {
            return $this->error('no_permission', 'You do not have permission to look up members');
        }

        $name = trim((string) $params['username']);
        $finder = \XF::finder('XF:User')
            ->where('username', 'like', \XF::db()->escapeLike($name, '?%'))
            ->order('username')
            ->limit(10);
        $out = [];
        foreach ($finder->fetch() as $u) {
            // Skip accounts the member list itself would withhold. XF\Entity\User
            // has no canView(); visibility is expressed through user_state and
            // the ban flag, which is exactly what getUserProfile checks.
            if ($u->user_state !== 'valid' || $u->is_banned) {
                continue;
            }
            $out[] = ['user_id' => $u->user_id, 'username' => $u->username];
        }
        return $this->success($out);
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
}
