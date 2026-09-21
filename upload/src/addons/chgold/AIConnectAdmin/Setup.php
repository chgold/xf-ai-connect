<?php

namespace chgold\AIConnectAdmin;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUninstallTrait;
    use StepRunnerUpgradeTrait;

    /**
     * Makes the 'admin' OAuth scope requestable.
     *
     * Every registered client ships allowing only read and write, which is why
     * the administrative tools could never be called: the scope they demand was
     * not obtainable by any client, under any circumstances. Installing this
     * add-on is the explicit act that opens it.
     *
     * Adding the scope does NOT hand it out. A client still has to ask for it
     * and the member still has to approve it on the consent screen, and every
     * tool then re-checks that the connected account is genuinely an
     * administrator. Uninstalling withdraws the scope again.
     */
    public function installStep1(): void
    {
        $this->addAdminScopeToClients();
    }

    public function upgrade1000000Step1(): void
    {
        $this->addAdminScopeToClients();
    }

    /**
     * After a fresh install, make the Admin tools actually visible + governable
     * WITHOUT a manual "Rebuild caches".
     *
     * Two things only happen when Core's own Setup runs (i.e. when Core is
     * installed/upgraded), which a standalone Admin install/upgrade does NOT
     * trigger:
     *   1. The code-event listener cache — our ai_connect_modules_init listener
     *      (which injects all 57 Admin tools into the manifest) is not live until
     *      XF rebuilds its listener cache. On a fresh Admin install it stays
     *      stale, so the manifest showed only the handful of tools cached from a
     *      previous state until an admin manually rebuilt caches.
     *   2. The per-tool ACP permissions — Core's syncToolPermissions() fires the
     *      ai_connect_sync_tool_permissions event and writes a permission per
     *      tool. Without it, only the 8 static permissions shipped in
     *      _data/permissions.xml exist, so the ACP showed 8 tools out of 57.
     *
     * Re-running both here makes a classic user install/upgrade self-sufficient.
     */
    public function postInstall(array &$stateChanges): void
    {
        $this->rebuildAdminData();
    }

    public function postUpgrade($previousVersion, array &$stateChanges): void
    {
        $this->rebuildAdminData();
    }

    protected function rebuildAdminData(): void
    {
        \XF::runOnce('aiconnectAdmin_rebuild', function () {
            // 1. Make the tool-injecting listener live so all Admin tools appear.
            \XF::repository('XF:CodeEventListener')->rebuildListenerCache();

            // 2. Register a permission for every Admin tool (not just the 8 static
            //    ones). Mirrors what Core's syncToolPermissions would do, using
            //    Core's public helper so the ID format stays identical.
            $module = new \chgold\AIConnectAdmin\Module\AdminModule(null);
            $tools  = [];
            foreach (array_keys($module->getTools()) as $toolName) {
                $tools[$toolName] = $toolName;
            }
            if ($tools && class_exists('chgold\\AIConnect\\Setup')) {
                \chgold\AIConnect\Setup::syncPackagePermissions(
                    $this->db(),
                    [
                        'admin' => [
                            'label'         => 'AI Connect Admin — Board Administration',
                            'display_order' => 420,
                            'modules'       => ['xenforo_admin' => $tools],
                        ],
                    ],
                    'chgold/AIConnectAdmin'
                );
            }

            // 3. Rebuild permission combinations so the new permissions take
            //    effect (XF does this via a job, mirroring Core's setup).
            \XF::app()->jobManager()->enqueueUnique(
                'aiconnectAdmin_perm_rebuild',
                'XF:PermissionRebuild',
                [],
                false
            );
        });
    }

    public function uninstallStep1(): void
    {
        $db = $this->db();

        // These two tables belong to the free Core add-on (chgold/AIConnect), not
        // to Admin. Admin only withdraws the 'admin' scope it added on install. If
        // Core was already uninstalled (its tables dropped), there is nothing to
        // clean up here — guard each access so uninstalling Admin never crashes
        // with "Table ... doesn't exist" [1146].
        if ($this->tableExists('xf_ai_connect_oauth_clients')) {
            $clients = $db->fetchAll('SELECT client_id, allowed_scopes FROM xf_ai_connect_oauth_clients');
            foreach ($clients as $client) {
                $scopes = json_decode((string) $client['allowed_scopes'], true);
                if (!is_array($scopes) || !in_array('admin', $scopes, true)) {
                    continue;
                }

                $scopes = array_values(array_diff($scopes, ['admin']));
                $db->update(
                    'xf_ai_connect_oauth_clients',
                    ['allowed_scopes' => json_encode($scopes)],
                    'client_id = ?',
                    $client['client_id']
                );
            }
        }

        // Tokens already carrying the scope must lose their power too, otherwise
        // uninstalling would leave live admin-capable tokens behind.
        if ($this->tableExists('xf_ai_connect_oauth_tokens')) {
            $db->query(
                "UPDATE xf_ai_connect_oauth_tokens
                    SET revoked_date = ?
                  WHERE revoked_date = 0
                    AND scopes LIKE '%admin%'",
                [\XF::$time]
            );
        }
    }

    protected function addAdminScopeToClients(): void
    {
        $db = $this->db();

        $clients = $db->fetchAll('SELECT client_id, allowed_scopes FROM xf_ai_connect_oauth_clients');
        foreach ($clients as $client) {
            $scopes = json_decode((string) $client['allowed_scopes'], true);
            if (!is_array($scopes)) {
                $scopes = array_filter(array_map('trim', explode(',', (string) $client['allowed_scopes'])));
            }
            if (in_array('admin', $scopes, true)) {
                continue;
            }

            $scopes[] = 'admin';
            $db->update(
                'xf_ai_connect_oauth_clients',
                ['allowed_scopes' => json_encode(array_values($scopes))],
                'client_id = ?',
                $client['client_id']
            );
        }
    }
}
