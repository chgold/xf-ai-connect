<?php

namespace chgold\AIConnectPro\Listener;

class SyncPermissions
{
    public static function aiConnectSyncToolPermissions(array &$toolDefs, array &$packageDefs): void
    {
        // One package per bundle, so the Admin CP renders the same eight sets we
        // advertise (Core / Moderation / Writing / Engagement / Profile /
        // Conversation / Media / Automation) instead of one flat list of 66
        // checkboxes with no structure.
        //
        // Each package gets its own interface group AND its own master switch
        // (use_package_{bundle}) — that master switch provides the "one toggle
        // per set" behaviour: denying it greys out every tool beneath it.
        //
        // The tool→bundle map comes from ProModule::getToolNamesByBundle(), the
        // same source the licence gate uses, so permissions and licensing can
        // never disagree about which tool belongs to which bundle.
        //
        // ProModule tolerates a null manifest (it only registers definitions).
        $module   = new \chgold\AIConnectPro\Module\ProModule(null);
        $byBundle = $module->getToolNamesByBundle();

        $order = 320;
        foreach (\chgold\AIConnectPro\Module\ProModule::BUNDLE_REGISTRARS as $bundleKey => $info) {
            if (empty($byBundle[$bundleKey])) {
                continue;
            }

            $packageDefs[$bundleKey] = [
                'label'         => 'AI Connect Pro — ' . $info['label'],
                'display_order' => $order,
                'modules'       => [
                    'xenforo_pro' => $byBundle[$bundleKey],
                ],
            ];

            $order += 10;
        }
    }
}
