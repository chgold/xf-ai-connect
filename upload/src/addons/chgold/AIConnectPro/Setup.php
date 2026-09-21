<?php

namespace chgold\AIConnectPro;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUninstallTrait;
    use StepRunnerUpgradeTrait;

    public function postInstall(array &$stateChanges)
    {
        $this->rebuildProData();
    }

    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        $this->rebuildProData();
    }

    protected function rebuildProData(): void
    {
        \XF::runOnce('aiconnectPro_rebuild', function () {
            // Make the tool-injecting listener (ai_connect_modules_init) live so
            // ALL Pro tools appear in the manifest without a manual Rebuild Caches.
            // A standalone Pro install/upgrade does NOT rebuild the listener cache
            // on its own, so a classic user install showed only a stale/partial
            // tool set until an admin manually rebuilt caches (same class of bug
            // fixed in Admin v1.4.25).
            \XF::repository('XF:CodeEventListener')->rebuildListenerCache();

            // Register a permission per Pro tool.
            $this->syncProPermissions();

            // Rebuild permission combinations so the new permissions take effect.
            \XF::app()->jobManager()->enqueueUnique(
                'aiconnectPro_perm_rebuild',
                'XF:PermissionRebuild',
                [],
                false
            );
        });
    }

    protected function syncProPermissions(): void
    {
        $packageDefs = [];
        $toolDefs = [];
        \chgold\AIConnectPro\Listener\SyncPermissions::aiConnectSyncToolPermissions($toolDefs, $packageDefs);
        \chgold\AIConnect\Setup::syncPackagePermissions(\XF::db(), $packageDefs, 'chgold/AIConnectPro');
    }
}
