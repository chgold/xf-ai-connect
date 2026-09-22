<?php

namespace chgold\AIConnectModeration\Listener;

class SyncPermissions
{
    /**
     * Declares the moderation tools as per-bundle permission packages, so the
     * Admin CP shows one clearly-labelled set per bundle that can be denied
     * wholesale rather than mixing moderation in with the Pro bundles.
     */
    public static function aiConnectSyncToolPermissions(array &$toolDefs, array &$packageDefs): void
    {
        $module = new \chgold\AIConnectModeration\Module\ModerationModule(null);

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
    }
}
