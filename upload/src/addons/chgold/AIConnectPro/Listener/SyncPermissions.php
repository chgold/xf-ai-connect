<?php

namespace chgold\AIConnectPro\Listener;

class SyncPermissions
{
    public static function aiConnectSyncToolPermissions(array &$toolDefs, array &$packageDefs): void
    {
        // Build the per-tool permission list dynamically from ProModule so that
        // every registered tool is covered automatically — no need to maintain a
        // parallel hardcoded list here as tools are added. ProModule tolerates a
        // null manifest (it only registers tool definitions in that case).
        $module = new \chgold\AIConnectPro\Module\ProModule(null);

        $packageDefs['pro'] = [
            'label'         => 'AI Connect — Pro Tools',
            'display_order' => 320,
            'modules'       => [
                'xenforo_pro' => $module->getToolNames(),
            ],
        ];
    }
}
