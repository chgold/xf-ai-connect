<?php

namespace chgold\AIConnectAdmin\Listener;

class SyncPermissions
{
    /**
     * Declares the administrative tools as their own permission package, so the
     * Admin CP shows one clearly-labelled set that can be denied wholesale
     * rather than mixing board administration in with the Pro bundles.
     */
    public static function aiConnectSyncToolPermissions(array &$toolDefs, array &$packageDefs): void
    {
        $module = new \chgold\AIConnectAdmin\Module\AdminModule(null);

        $tools = [];
        foreach (array_keys($module->getTools()) as $toolName) {
            $tools[$toolName] = $toolName;
        }

        if (!$tools) {
            return;
        }

        $packageDefs['admin'] = [
            'label'         => 'AI Connect Admin — Board Administration',
            'display_order' => 420,
            'modules'       => [
                'xenforo_admin' => $tools,
            ],
        ];
    }
}
