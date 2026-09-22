<?php

namespace chgold\AIConnectModeration;

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
     * Moderation actions (reports, approval queue, warnings) require moderator
     * or admin privileges at the XF level, and the token must carry the 'admin'
     * scope to indicate the agent asked for elevated access. This mirrors
     * AIConnectAdmin's approach — the scope does not hand out access by itself,
     * but it allows the OAuth consent screen to surface the elevated-access
     * request to the member.
     *
     * Adding the scope does NOT hand it out. A client still has to ask for it
     * and the member still has to approve it on the consent screen, and every
     * tool then re-checks that the connected account has the required permissions.
     * Uninstalling withdraws the scope again.
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
     * After a fresh install, make the Moderation tools actually visible + governable
     * WITHOUT a manual "Rebuild caches".
     *
     * Two things only happen when Core's own Setup runs (i.e. when Core is
     * installed/upgraded), which a standalone Moderation install/upgrade does NOT
     * trigger:
     *   1. The code-event listener cache — our ai_connect_modules_init listener
     *      (which injects all Moderation tools into the manifest) is not live until
     *      XF rebuilds its listener cache. On a fresh Moderation install it stays
     *      stale, so the manifest showed only the tools cached from a previous state
     *      until an admin manually rebuilt caches.
     *   2. The per-tool ACP permissions — Core's syncToolPermissions() fires the
     *      ai_connect_sync_tool_permissions event and writes a permission per
     *      tool. Without it, only the static permissions shipped in
     *      _data/permissions.xml exist.
     *
     * Re-running both here makes a classic user install/upgrade self-sufficient.
     */
    public function postInstall(array &$stateChanges): void
    {
        $this->rebuildModerationData();
    }

    public function postUpgrade($previousVersion, array &$stateChanges): void
    {
        $this->rebuildModerationData();
    }

    protected function rebuildModerationData(): void
    {
        \XF::runOnce('aiconnectModeration_rebuild', function () {
            // 1. Make the tool-injecting listener live so all Moderation tools appear.
            \XF::repository('XF:CodeEventListener')->rebuildListenerCache();

            // 2. Register a permission for every Moderation tool (not just the static
            //    ones). Mirrors what Core's syncToolPermissions would do, using
            //    Core's public helper so the ID format stays identical.
            //    One package per bundle (same as SyncPermissions listener).
            $module = new \chgold\AIConnectModeration\Module\ModerationModule(null);
            $packageDefs = [];
            foreach ($module->getToolNamesByBundle() as $bundleKey => $tools) {
                if (!$tools) {
                    continue;
                }
                $packageDefs[$bundleKey] = [
                    'label'         => \chgold\AIConnectModeration\Module\ModerationModule::BUNDLE_REGISTRARS[$bundleKey]['label'],
                    'display_order' => 420,
                    'modules'       => [
                        'xenforo_mod' => array_combine(array_keys($tools), array_keys($tools)),
                    ],
                ];
            }
            if ($packageDefs && class_exists('chgold\\AIConnect\\Setup')) {
                \chgold\AIConnect\Setup::syncPackagePermissions(
                    $this->db(),
                    $packageDefs,
                    'chgold/AIConnectModeration'
                );
            }

            // 3. Rebuild permission combinations so the new permissions take
            //    effect (XF does this via a job, mirroring Core's setup).
            \XF::app()->jobManager()->enqueueUnique(
                'aiconnectModeration_perm_rebuild',
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
        // to Moderation. Moderation only withdraws the 'admin' scope it added on
        // install. If Core was already uninstalled (its tables dropped), there is
        // nothing to clean up here — guard each access so uninstalling Moderation
        // never crashes with "Table ... doesn't exist" [1146].
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

        // Drop the code-event listener cache so our ai_connect_modules_init
        // listener stops injecting the Moderation tools into the manifest the moment
        // the add-on is removed. Without this the manifest kept advertising the
        // Moderation tools (from the stale listener cache) until a manual Rebuild
        // Caches — the mirror image of the fresh-install visibility bug fixed in
        // postInstall(). XF drops the listener rows on uninstall, but the cache
        // that the manifest builds from is not guaranteed to be rebuilt in the
        // same request, so we force it here.
        \XF::repository('XF:CodeEventListener')->rebuildListenerCache();
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
