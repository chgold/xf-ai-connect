<?php

namespace chgold\AIConnectAdmin\Module;

use chgold\AIConnect\Module\ModuleBase;
use XF\Http\Upload;
use XF\Repository\UserRepository;

/**
 * Administrative tool-set: node CRUD, user CRUD, avatar management, user-group
 * CRUD + secondary-group assignment, and user discipline (ban/unban).
 *
 * These live outside the Pro add-on because they are not the same class of
 * operation. Everything in Pro acts as the connected member; the tools here act
 * ON the board and on other people's accounts, so they are gated twice — the
 * token must carry the 'admin' scope AND the connected account must genuinely be
 * an administrator. Shipping them separately also means a customer who buys Pro
 * is not silently handed the ability to delete users.
 *
 * Bundle structure (mirrors AIConnectPro ProModule pattern) — each trait maps
 * to a bundle_key advertised in pm_tool_bundles + separately toggle-able:
 *   base        → node CRUD, user CRUD, avatar management (8 tools, class body)
 *   usergroups  → usergroup CRUD + secondary-group assignment (5 tools)
 *   discipline  → ban / unban (2 tools)
 */
class AdminModule extends ModuleBase
{
    use AdminUsergroupsTrait;
    use AdminDisciplineTrait;
    use AdminUserCredentialsTrait;
    use AdminNodesAdvancedTrait;
    use AdminAddonsTrait;
    use AdminOptionsTrait;
    use AdminCronTrait;
    use AdminStylesTrait;

    protected $moduleName = 'xenforo_admin';

    /**
     * bundle_key → registrar method. Mirrors ProModule::BUNDLE_REGISTRARS so
     * dashboard/permissions can group + toggle the same way.
     */
    public const BUNDLE_REGISTRARS = [
        'base'             => ['label' => 'Base — nodes & users',        'method' => null],
        'usergroups'       => ['label' => 'Usergroups & memberships',    'method' => 'registerUsergroupsTools'],
        'discipline'       => ['label' => 'User discipline (ban/unban)', 'method' => 'registerDisciplineTools'],
        'user_credentials' => ['label' => 'User credentials (email/password)', 'method' => 'registerUserCredentialsTools'],
        'nodes_advanced'   => ['label' => 'Nodes advanced (reorder/permissions)', 'method' => 'registerNodesAdvancedTools'],
        'addons'           => ['label' => 'Add-ons (list/enable/disable)', 'method' => 'registerAddonsTools'],
        'options'          => ['label' => 'Site options (get/set)',       'method' => 'registerOptionsTools'],
        'cron'             => ['label' => 'Cron tasks (list/trigger)',    'method' => 'registerCronTools'],
        'styles'           => ['label' => 'Styles (list/set default)',    'method' => 'registerStylesTools'],
    ];

    protected function registerTools()
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
        $this->registerTool('createUser', [
            'description' => 'Create a new user (admin). Defaults user_state="valid" (immediately usable).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['username', 'email'],
                'properties' => [
                    'username' => ['type' => 'string', 'description' => 'Username'],
                    'email' => ['type' => 'string', 'description' => 'Email address'],
                    'password' => ['type' => 'string', 'description' => 'Password (optional)'],
                    'require_email_confirm' => ['type' => 'boolean', 'description' => 'If true, create in user_state="email_confirm" (user must click a link before login). Default false — admin-created users are immediately valid.'],
                ],
            ],
        ]);
        $this->registerTool('updateUser', [
            'description' => 'Update a user about text and/or primary user group. '
                . 'Editing a super admin requires the caller to be a super admin (mirrors XF-core). '
                . 'Self-demoting your own super-admin group via API is refused unless '
                . 'confirm_self_demote:true — lockout risk equivalent to self-delete.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'User to update'],
                    'about' => ['type' => 'string', 'description' => 'New about text (optional)'],
                    'user_group_id' => ['type' => 'integer', 'description' => 'New primary user group id (optional)'],
                    'confirm_self_demote' => ['type' => 'boolean', 'description' => 'Explicit override to allow demoting yourself if is_super_admin (lockout risk).'],
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
        $this->registerTool('uploadUserAvatar', [
            'description' => "Set another member's avatar from a URL (admin)",
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id', 'file_url'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'Member whose avatar to replace'],
                    'file_url' => ['type' => 'string', 'description' => 'Publicly reachable image URL'],
                ],
            ],
        ]);
        $this->registerTool('deleteUserAvatar', [
            'description' => "Remove another member's avatar (admin)",
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id'],
                'properties' => ['user_id' => ['type' => 'integer', 'description' => 'Member whose avatar to remove']],
            ],
        ]);

        // Bundle: usergroups (5 tools) — usergroup CRUD + secondary group assignment
        $this->registerUsergroupsTools();

        // Bundle: discipline (2 tools) — ban / unban
        $this->registerDisciplineTools();

        // Bundle: user_credentials (2 tools) — change email + password
        $this->registerUserCredentialsTools();

        // Bundle: nodes_advanced (2 tools) — reorder + per-node permissions
        $this->registerNodesAdvancedTools();

        // Bundle: addons (3 tools) — list + enable + disable add-ons
        $this->registerAddonsTools();

        // Bundle: options (2 tools) — get + set board options
        $this->registerOptionsTools();

        // Bundle: cron (2 tools) — list + trigger cron entries
        $this->registerCronTools();

        // Bundle: styles (2 tools) — list styles + set default
        $this->registerStylesTools();
    }

    /**
     * Gate for every tool here: the token must carry the 'admin' scope and the
     * connected account must actually be an administrator.
     *
     * Both halves matter. The scope alone proves only that the agent asked for
     * administrative access; is_admin proves the board granted it.
     *
     * NOTE: is_admin is a coarse flag — it means "has SOME ACP permission",
     * NOT "has all". Callers MUST additionally call assertPermission($area)
     * for the specific ACP area they touch (node/user/userGroup/addOn/etc).
     * A partial admin (e.g. "user" perm only) should not be able to disable
     * add-ons via the API just because they hold an admin-scoped token.
     */
    protected function requireAdmin()
    {
        if (!\XF::service('chgold\AIConnect:BearerAuth')->checkScope('admin')) {
            return $this->error('insufficient_scope', 'The "admin" scope is required for this operation');
        }
        if (!\XF::visitor()->is_admin) {
            return $this->error('no_permission', 'This operation requires an administrator account');
        }
        return null;
    }

    /**
     * Per-area ACP permission check. MUST be called by every mutation tool
     * after requireAdmin(), passing the exact XF permission string that the
     * corresponding ACP controller checks (verified in XF source):
     *   - node       → NodeController::preDispatchController
     *   - user       → UserController actions
     *   - userGroup  → UserGroupController + PermissionController (permission edits)
     *   - addOn      → AddOnController::preDispatchController
     *   - option     → OptionController::preDispatchController
     *   - style      → StyleController::preDispatchController
     *   - cron       → CronEntryController::preDispatchController
     *
     * Super admins pass this check automatically (XF-native semantics).
     */
    public function assertPermission(string $area)
    {
        if (!\XF::visitor()->hasAdminPermission($area)) {
            return $this->error(
                'no_permission',
                'This operation requires the "' . $area . '" admin permission'
            );
        }
        return null;
    }

    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- dynamic dispatch execute_<name>

    public function execute_createNode($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('node')) {
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
        if ($err = $this->assertPermission('node')) {
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
        if ($err = $this->assertPermission('node')) {
            return $err;
        }
        $node = \XF::em()->find('XF:Node', $params['node_id']);
        if (!$node) {
            return $this->error('not_found', 'Node not found');
        }
        // What happens to child nodes is a TreeStructured behavior option, not a
        // property on the entity. Assigning $node->deleteChildAction wrote to a
        // column that does not exist and threw before anything was deleted, so
        // this tool could never have worked — it went unnoticed because the
        // 'admin' scope it requires was not obtainable by any client.
        $childAction = !empty($params['delete_children']) ? 'delete' : 'move';
        $node->getBehavior(\XF\Behavior\TreeStructured::class)->setOption('deleteChildAction', $childAction);

        if (!$node->delete(false)) {
            return $this->error('delete_failed', 'Node could not be deleted');
        }

        return $this->success([
            'node_id'      => (int) $params['node_id'],
            'deleted'      => true,
            'child_action' => $childAction,
        ]);
    }

    public function execute_createUser($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('user')) {
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

        // XF's RegistrationService defaults new users to user_state='email_confirm' —
        // that's correct for public self-registration but WRONG for an admin API
        // (the caller has admin scope + is_admin, so the user is pre-vetted). Without
        // this, the created user is invisible to searchUsers/listUsers (which filter
        // by isValidUser) — a documented pattern that looks like a write/read
        // inconsistency bug. Force to 'valid' so admin-created users are immediately
        // usable. Caller can flip via updateUser if they actually want email confirm.
        $forceState = isset($params['require_email_confirm']) && $params['require_email_confirm']
            ? 'email_confirm'
            : 'valid';
        if ($user->user_state !== $forceState) {
            $user->user_state = $forceState;
            $user->save();
        }

        return $this->success([
            'user_id' => $user->user_id,
            'username' => $user->username,
            'user_state' => $user->user_state,
        ]);
    }

    public function execute_updateUser($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('user')) {
            return $err;
        }
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }

        // Safeguard 1: mirror XF-core ACP — only super admins may edit super
        // admins. Prevents a plain admin from tampering with a super-admin
        // account (locking password, changing group, etc). XF-native behaviour
        // per Admin/Controller/UserController::actionEdit line 1317.
        $visitor = \XF::visitor();
        if ($user->is_super_admin && !$visitor->is_super_admin) {
            return $this->error(
                'no_permission',
                'Only super administrators can edit other super administrators'
            );
        }

        // Safeguard 2: prevent self-demote via API. An agent running as a
        // super admin could accidentally strip their own admin group by
        // calling updateUser on their own user_id — same lockout effect as
        // deleteUser. Only allow group change on self via the ACP UI (where
        // password re-auth applies), never via API. Explicit override:
        // pass `confirm_self_demote: true` to acknowledge the risk.
        if (
            isset($params['user_group_id'])
            && $user->user_id === $visitor->user_id
            && $user->is_super_admin
            && (int) $params['user_group_id'] !== (int) $user->user_group_id
            && empty($params['confirm_self_demote'])
        ) {
            return $this->error(
                'no_permission',
                'Refusing to change your own primary group on a super admin account via API — '
                . 'lockout risk equivalent to self-deletion. Pass confirm_self_demote:true to override, '
                . 'or use the ACP directly.'
            );
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
        if ($err = $this->assertPermission('user')) {
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

    public function execute_uploadUserAvatar($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }
        if ($err = $this->assertCanManageUser($user)) {
            return $err;
        }

        $upload = $this->fetchUpload($params['file_url'], 'avatar.jpg', $fetchError);
        if (!$upload) {
            return $this->error('upload_failed', $fetchError);
        }

        $avatarService = \XF::service('XF:User\Avatar', $user);
        $avatarService->setImageFromUpload($upload);
        if (!$avatarService->updateAvatar()) {
            return $this->error('upload_failed', 'Avatar could not be updated');
        }
        return $this->success(['user_id' => $user->user_id, 'avatar' => true]);
    }

    public function execute_deleteUserAvatar($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        $user = \XF::em()->find('XF:User', $params['user_id']);
        if (!$user) {
            return $this->error('not_found', 'User not found');
        }
        if ($err = $this->assertCanManageUser($user)) {
            return $err;
        }

        $avatarService = \XF::service('XF:User\Avatar', $user);
        $avatarService->deleteAvatar();
        return $this->success(['user_id' => $user->user_id, 'avatar' => false]);
    }

    // phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

    /**
     * Confirms the caller may act on this particular member.
     *
     * The previous implementation asked $user->canUploadAvatar(), which reports
     * whether the TARGET is allowed to have an avatar — it says nothing about
     * the caller's authority over them. Any token with write scope could
     * therefore replace or delete the avatar of any member, administrators
     * included. XenForo itself only permits this from the Admin CP behind the
     * 'user' admin permission, so that is the check applied here.
     */
    protected function assertCanManageUser(\XF\Entity\User $user)
    {
        $visitor = \XF::visitor();

        if (!$visitor->hasAdminPermission('user')) {
            return $this->error('no_permission', 'Managing other members requires the "user" admin permission');
        }

        // A plain admin must not act on a super admin; only another super admin may.
        if ($user->is_super_admin && !$visitor->is_super_admin) {
            return $this->error('no_permission', 'Only a super administrator can manage a super administrator');
        }

        return null;
    }

    private function fetchUpload(string $url, ?string $filename, &$error): ?Upload
    {
        $error = null;
        $contents = @file_get_contents($url);
        if ($contents === false || $contents === '') {
            $error = 'Could not download the file from the supplied URL';
            return null;
        }

        $tempFile = \XF\Util\File::getTempFile();
        if (!$tempFile || file_put_contents($tempFile, $contents) === false) {
            $error = 'Could not buffer the downloaded file';
            return null;
        }

        $name = $filename ?: (basename(parse_url($url, PHP_URL_PATH) ?: '') ?: 'upload');

        return new Upload($tempFile, $name);
    }
}
