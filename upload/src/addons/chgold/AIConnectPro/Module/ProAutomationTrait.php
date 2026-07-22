<?php

namespace chgold\AIConnectPro\Module;

use XF\Repository\UserRepository;

/**
 * Automation / administration tools for the Pro module (11 tools): node CRUD,
 * user CRUD, plus getMe and findUserByName. Node/user create/edit/delete require
 * the 'admin' scope AND an admin visitor; reads require only 'read'. Split into a
 * trait to keep ProModule under the file-size ceiling.
 */
trait ProAutomationTrait
{
    protected function registerAutomationTools()
    {
        $this->registerTool('createNode', [
            'description' => 'Create a forum/category/page/link node (admin)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_type_id', 'title'],
                'properties' => [
                    'node_type_id' => ['type' => 'string', 'description' => 'Forum, Category, Page or LinkForum'],
                    'title' => ['type' => 'string', 'description' => 'Node title'],
                    'parent_node_id' => ['type' => 'integer', 'description' => 'Parent node id (0 = root)'],
                    'description' => ['type' => 'string', 'description' => 'Node description (optional)'],
                ],
            ],
        ]);
        $this->registerTool('editNode', [
            'description' => 'Edit a node title/description/parent (admin)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => [
                    'node_id' => ['type' => 'integer', 'description' => 'Node to edit'],
                    'title' => ['type' => 'string', 'description' => 'New title (optional)'],
                    'description' => ['type' => 'string', 'description' => 'New description (optional)'],
                    'parent_node_id' => ['type' => 'integer', 'description' => 'New parent node id (optional)'],
                ],
            ],
        ]);
        $this->registerTool('deleteNode', [
            'description' => 'Delete a node (admin)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => [
                    'node_id' => ['type' => 'integer', 'description' => 'Node to delete'],
                    'delete_children' => ['type' => 'boolean', 'description' => 'Delete child nodes too (default false = reparent)'],
                ],
            ],
        ]);
        $this->registerTool('listNodes', [
            'description' => 'List the full node tree',
            'input_schema' => ['type' => 'object', 'properties' => new \stdClass()],
        ]);
        $this->registerTool('getNode', [
            'description' => 'Get a single node',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => ['node_id' => ['type' => 'integer', 'description' => 'Node id']],
            ],
        ]);
        $this->registerTool('listNodesFlat', [
            'description' => 'List nodes as a flat array with depth',
            'input_schema' => ['type' => 'object', 'properties' => new \stdClass()],
        ]);
        $this->registerTool('createUser', [
            'description' => 'Create a new user (admin)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['username', 'email'],
                'properties' => [
                    'username' => ['type' => 'string', 'description' => 'Username'],
                    'email' => ['type' => 'string', 'description' => 'Email address'],
                    'password' => ['type' => 'string', 'description' => 'Password (optional; no password set if omitted)'],
                ],
            ],
        ]);
        $this->registerTool('updateUser', [
            'description' => 'Update a user (about text and/or primary user group) (admin)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'User to update'],
                    'about' => ['type' => 'string', 'description' => 'New about text (optional)'],
                    'user_group_id' => ['type' => 'integer', 'description' => 'New primary user group id (optional)'],
                ],
            ],
        ]);
        $this->registerTool('deleteUser', [
            'description' => 'Delete a user (admin)',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id'],
                'properties' => ['user_id' => ['type' => 'integer', 'description' => 'User to delete']],
            ],
        ]);
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

    public function execute_createNode($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        $validTypes = ['Forum', 'Category', 'Page', 'LinkForum'];
        if (!in_array($params['node_type_id'], $validTypes, true)) {
            return $this->error('invalid_param', 'node_type_id must be one of: ' . implode(', ', $validTypes));
        }
        $node = \XF::em()->create('XF:Node');
        $node->node_type_id = $params['node_type_id'];
        $node->title = $params['title'];
        if (isset($params['description'])) {
            $node->description = $params['description'];
        }
        if (!empty($params['parent_node_id'])) {
            $node->parent_node_id = (int) $params['parent_node_id'];
        }
        $node->getDataRelationOrDefault();
        if (!$node->preSave()) {
            return $this->error('validation_failed', implode(' ', $node->getErrors()));
        }
        $node->save();
        return $this->success(['node_id' => $node->node_id, 'title' => $node->title, 'node_type_id' => $node->node_type_id]);
    }

    public function execute_editNode($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        $node = \XF::em()->find('XF:Node', $params['node_id']);
        if (!$node) {
            return $this->error('not_found', 'Node not found');
        }
        if (isset($params['title']) && trim($params['title']) !== '') {
            $node->title = $params['title'];
        }
        if (isset($params['description'])) {
            $node->description = $params['description'];
        }
        if (isset($params['parent_node_id'])) {
            $node->parent_node_id = (int) $params['parent_node_id'];
        }
        if (!$node->preSave()) {
            return $this->error('validation_failed', implode(' ', $node->getErrors()));
        }
        $node->save();
        return $this->success(['node_id' => $node->node_id, 'title' => $node->title]);
    }

    public function execute_deleteNode($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        $node = \XF::em()->find('XF:Node', $params['node_id']);
        if (!$node) {
            return $this->error('not_found', 'Node not found');
        }
        if (!empty($params['delete_children'])) {
            $node->delete();
        } else {
            $node->deleteChildAction = 'move';
            $node->delete();
        }
        return $this->success(['node_id' => (int) $params['node_id'], 'deleted' => true]);
    }

    public function execute_listNodes($params)
    {
        $nodes = \XF::repository('XF:Node')->getFullNodeList();
        $out = [];
        foreach ($nodes as $node) {
            $out[] = $this->nodeData($node);
        }
        return $this->success($out);
    }

    public function execute_getNode($params)
    {
        $node = \XF::em()->find('XF:Node', $params['node_id']);
        if (!$node) {
            return $this->error('not_found', 'Node not found');
        }
        return $this->success($this->nodeData($node));
    }

    public function execute_listNodesFlat($params)
    {
        $nodes = \XF::repository('XF:Node')->getFullNodeList();
        $tree = \XF::repository('XF:Node')->createNodeTree($nodes);
        $out = [];
        foreach ($tree->getFlattened() as $entry) {
            $data = $this->nodeData($entry['record']);
            $data['depth'] = $entry['depth'];
            $out[] = $data;
        }
        return $this->success($out);
    }

    public function execute_createUser($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        /** @var \XF\Service\User\RegistrationService $registration */
        $registration = \XF::service('XF:User\Registration');
        $registration->setMapped(['username' => $params['username'], 'email' => $params['email']]);
        if (!empty($params['password'])) {
            $registration->setPassword($params['password'], '', false);
        } else {
            $registration->setNoPassword();
        }
        if (!$registration->validate($errors)) {
            return $this->error('validation_failed', implode(' ', $errors));
        }
        $user = $registration->save();
        return $this->success(['user_id' => $user->user_id, 'username' => $user->username]);
    }

    public function execute_updateUser($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }
        if (isset($params['user_group_id'])) {
            $user->user_group_id = (int) $params['user_group_id'];
        }
        if (isset($params['about']) && $user->Profile) {
            $user->Profile->about = (string) $params['about'];
        }
        if (!$user->preSave() || ($user->Profile && !$user->Profile->preSave())) {
            return $this->error('validation_failed', implode(' ', $user->getErrors()));
        }
        $user->save();
        if ($user->Profile) {
            $user->Profile->save();
        }
        return $this->success(['user_id' => $user->user_id, 'username' => $user->username]);
    }

    public function execute_deleteUser($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }
        if ($user->is_super_admin) {
            return $this->error('no_permission', 'Super administrators cannot be deleted via the API');
        }
        /** @var \XF\Service\User\DeleteService $deleter */
        $deleter = \XF::service('XF:User\Delete', $user);
        if (!$deleter->delete($errors)) {
            return $this->error('delete_failed', is_array($errors) ? implode(' ', $errors) : 'Could not delete user');
        }
        return $this->success(['user_id' => (int) $params['user_id'], 'deleted' => true]);
    }

    public function execute_getMe($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('not_authenticated', 'No authenticated user');
        }
        return $this->success([
            'user_id' => $visitor->user_id,
            'username' => $visitor->username,
            'email' => $visitor->email,
            'user_group_id' => $visitor->user_group_id,
            'is_admin' => (bool) $visitor->is_admin,
            'is_super_admin' => (bool) $visitor->is_super_admin,
            'is_moderator' => (bool) $visitor->is_moderator,
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

    private function nodeData($node): array
    {
        return [
            'node_id' => $node->node_id,
            'title' => $node->title,
            'node_type_id' => $node->node_type_id,
            'parent_node_id' => $node->parent_node_id,
            'description' => $node->description,
            'display_order' => $node->display_order,
        ];
    }
}
