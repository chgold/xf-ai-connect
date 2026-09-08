<?php

namespace chgold\AIConnectPro\Module;

/**
 * Users & Groups Discovery bundle: enumerate usergroups + members + user directory.
 *
 * Fills the discovery gap in XF core itself — even the XenForo Admin UI has no
 * "list members of a group" page (XF\Admin\Controller\UserGroupController only
 * exposes index/edit/save/delete). Composes UserFinder against the indexed
 * xf_user_group_relation table for correct primary + secondary group coverage.
 *
 * NOTE: The `execute_{shortName}` methods below are dispatched by
 * ModuleBase::executeTool() via `[$this, 'execute_' . $name]` — this framework
 * convention is shared by all 13 Module/Trait classes across the addon family.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProUsersGroupsTrait
{
    protected function registerUsersGroupsTools()
    {
        $this->registerTool('listUsergroups', [
            'description' => 'List all usergroups with title, priority and display CSS. Wraps '
                . 'UserGroupRepository::findUserGroupsForList().',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getUsergroup', [
            'description' => 'Get a single usergroup by ID (title, priority, banner, user_title).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_group_id'],
                'properties' => [
                    'user_group_id' => [
                        'type' => 'integer',
                        'description' => 'Usergroup ID (see listUsergroups for IDs)',
                    ],
                ],
            ],
        ]);

        $this->registerTool('getUsergroupMembers', [
            'description' => 'List members of a usergroup with post_count, register_date, last_activity. '
                . 'Uses xf_user_group_relation for indexed primary + secondary coverage. '
                . 'Requires admin/moderator (canViewMemberList).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_group_id'],
                'properties' => [
                    'user_group_id' => [
                        'type' => 'integer',
                        'description' => 'Usergroup ID to list members of',
                    ],
                    'page' => [
                        'type' => 'integer',
                        'description' => 'Page number (1-based, default 1)',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Members per page (default 50, cap 100)',
                    ],
                ],
            ],
        ]);

        $this->registerTool('listUsers', [
            'description' => 'List all valid users paginated (alphabetical). '
                . 'Requires enableMemberList option + canViewMemberList permission.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'page' => [
                        'type' => 'integer',
                        'description' => 'Page number (1-based, default 1)',
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Users per page (default 50, cap 100)',
                    ],
                ],
            ],
        ]);

        $this->registerTool('searchUsers', [
            'description' => 'Search users by username prefix (min 2 chars). Wraps XF Finder LIKE.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['username'],
                'properties' => [
                    'username' => [
                        'type' => 'string',
                        'description' => 'Username prefix to search (min 2 chars)',
                        'minLength' => 2,
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max results (default 20, cap 50)',
                    ],
                ],
            ],
        ]);
    }

    public function execute_listUsergroups($params)
    {
        $repo = \XF::repository('XF:UserGroup');
        $groups = $repo->findUserGroupsForList()->fetch();
        $out = [];
        foreach ($groups as $g) {
            $out[] = [
                'user_group_id' => (int) $g->user_group_id,
                'title' => (string) $g->title,
                'display_style_priority' => (int) $g->display_style_priority,
                'username_css' => (string) $g->username_css,
                'user_title' => (string) $g->user_title,
                'banner_text' => (string) $g->banner_text,
            ];
        }
        return $this->success(['usergroups' => $out, 'count' => count($out)]);
    }

    public function execute_getUsergroup($params)
    {
        $group = \XF::em()->find('XF:UserGroup', (int) $params['user_group_id']);
        if (!$group) {
            return $this->error('not_found', 'Usergroup not found');
        }
        return $this->success([
            'user_group_id' => (int) $group->user_group_id,
            'title' => (string) $group->title,
            'display_style_priority' => (int) $group->display_style_priority,
            'username_css' => (string) $group->username_css,
            'user_title' => (string) $group->user_title,
            'banner_css_class' => (string) $group->banner_css_class,
            'banner_text' => (string) $group->banner_text,
        ]);
    }

    public function execute_getUsergroupMembers($params)
    {
        // Gate: requires admin/moderator — this exposes membership across groups
        // including staff groups, which is admin-only data.
        $visitor = \XF::visitor();
        if (!$visitor->canViewMemberList() && !$visitor->is_moderator && !$visitor->is_admin) {
            return $this->error('no_permission', 'This operation requires admin or moderator');
        }

        $groupId = (int) $params['user_group_id'];
        if (!\XF::em()->find('XF:UserGroup', $groupId)) {
            return $this->error('not_found', 'Usergroup not found');
        }

        $limit = isset($params['limit']) ? max(1, min(100, (int) $params['limit'])) : 50;
        $page = isset($params['page']) ? max(1, (int) $params['page']) : 1;
        $offset = ($page - 1) * $limit;

        // Direct JOIN on xf_user_group_relation — indexed, covers both primary
        // (user_group_id) and secondary (secondary_group_ids CSV). This is the
        // approach XF core devs recommend on the community forum; the alternative
        // (FIND_IN_SET on the CSV column) does a full table scan.
        $db = \XF::db();
        $rows = $db->fetchAll(
            'SELECT u.user_id, u.username, u.message_count, u.register_date,
                    u.last_activity, u.user_group_id AS primary_group_id,
                    u.secondary_group_ids, u.user_state, u.is_moderator, u.is_admin
             FROM xf_user_group_relation ugr
             INNER JOIN xf_user u ON u.user_id = ugr.user_id
             WHERE ugr.user_group_id = ? AND u.user_state = ?
             ORDER BY u.username
             LIMIT ? OFFSET ?',
            [$groupId, 'valid', $limit, $offset]
        );

        $total = (int) $db->fetchOne(
            'SELECT COUNT(*)
             FROM xf_user_group_relation ugr
             INNER JOIN xf_user u ON u.user_id = ugr.user_id
             WHERE ugr.user_group_id = ? AND u.user_state = ?',
            [$groupId, 'valid']
        );

        return $this->success([
            'user_group_id' => $groupId,
            'members' => $rows,
            'count' => count($rows),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'has_more' => ($page * $limit) < $total,
        ]);
    }

    public function execute_listUsers($params)
    {
        // Mirror XF's own /api/users behaviour: admin bypass, otherwise gated by
        // enableMemberList option + canViewMemberList permission.
        $visitor = \XF::visitor();
        if (!$visitor->hasAdminPermission('user')) {
            if (!\XF::options()->enableMemberList || !$visitor->canViewMemberList()) {
                return $this->error('no_permission', 'Member list is disabled or you lack permission');
            }
        }

        $limit = isset($params['limit']) ? max(1, min(100, (int) $params['limit'])) : 50;
        $page = isset($params['page']) ? max(1, (int) $params['page']) : 1;

        /** @var \XF\Finder\UserFinder $finder */
        $finder = \XF::finder('XF:User')
            ->isValidUser()
            ->order('username')
            ->limitByPage($page, $limit);
        $total = $finder->total();
        $users = $finder->fetch();

        $out = [];
        foreach ($users as $u) {
            $out[] = [
                'user_id' => (int) $u->user_id,
                'username' => (string) $u->username,
                'message_count' => (int) $u->message_count,
                'register_date' => (int) $u->register_date,
                'last_activity' => (int) $u->last_activity,
                'user_group_id' => (int) $u->user_group_id,
                'is_staff' => (bool) $u->is_staff,
            ];
        }

        return $this->success([
            'users' => $out,
            'count' => count($out),
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'has_more' => ($page * $limit) < $total,
        ]);
    }

    public function execute_searchUsers($params)
    {
        $username = ltrim((string) ($params['username'] ?? ''));
        if (mb_strlen($username) < 2) {
            return $this->error('invalid_param', 'username must be at least 2 characters');
        }
        $limit = isset($params['limit']) ? max(1, min(50, (int) $params['limit'])) : 20;

        /** @var \XF\Finder\UserFinder $finder */
        $finder = \XF::finder('XF:User');
        $users = $finder
            ->where('username', 'like', $finder->escapeLike($username, '?%'))
            ->isValidUser(true)
            ->order('username')
            ->fetch($limit);

        $exact = \XF::em()->findOne('XF:User', ['username' => $username]);
        if ($exact && $users) {
            unset($users[$exact->user_id]);
        }

        $out = [];
        if ($exact && $exact->user_state === 'valid' && !$exact->is_banned) {
            $out[] = [
                'user_id' => (int) $exact->user_id,
                'username' => (string) $exact->username,
                'match_type' => 'exact',
            ];
        }
        foreach ($users as $u) {
            $out[] = [
                'user_id' => (int) $u->user_id,
                'username' => (string) $u->username,
                'match_type' => 'prefix',
            ];
        }

        return $this->success(['users' => $out, 'count' => count($out)]);
    }
}
