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
        $this->registerTool('getMe', [
            'description' => 'Get the current authenticated user identity and permissions',
            'input_schema' => ['type' => 'object', 'properties' => new \stdClass()],
        ]);
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

    public function execute_getMe($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'No authenticated user');
        }
        return $this->success([
            'user_id'        => $visitor->user_id,
            'username'       => $visitor->username,
            'email'          => $visitor->email,
            'user_group_id'  => $visitor->user_group_id,
            'is_admin'       => (bool) $visitor->is_admin,
            'is_super_admin' => (bool) $visitor->is_super_admin,
            'is_moderator'   => (bool) $visitor->is_moderator,
        ]);
    }

    public function execute_findUserByName($params)
    {
        $name = trim((string) $params['username']);
        $finder = \XF::finder('XF:User')
            ->where('username', 'like', \XF::db()->escapeLike($name, '?%'))
            ->order('username')
            ->limit(10);
        $out = [];
        foreach ($finder->fetch() as $u) {
            $out[] = ['user_id' => $u->user_id, 'username' => $u->username];
        }
        return $this->success($out);
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
}
