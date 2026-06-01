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
        $this->syncProPermissions();
    }

    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        $this->syncProPermissions();
    }

    protected function syncProPermissions(): void
    {
        $packageDefs = [];
        $toolDefs = [];
        \chgold\AIConnectPro\Listener\SyncPermissions::aiConnectSyncToolPermissions($toolDefs, $packageDefs);
        \chgold\AIConnect\Setup::syncPackagePermissions(\XF::db(), $packageDefs, 'chgold/AIConnectPro');
    }
}
