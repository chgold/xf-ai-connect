<?php

namespace chgold\AIConnectAdmin\Listener;

class ModuleInit
{
    /**
     * Registers the administrative tool-set onto the core.
     *
     * Gated on the same licence the Pro add-on uses, under its own 'admin'
     * bundle, so an administrator tool-set can be sold — or withheld —
     * independently of the Pro bundles. Without that bundle the tools are never
     * registered, which is stronger than refusing them at call time: they do not
     * appear in the manifest at all.
     */
    public static function aiConnectModulesInit(array &$modules, \chgold\AIConnect\Service\Manifest $manifestService)
    {
        if (!class_exists('\chgold\AIConnectPro\License\Validator')) {
            return;
        }

        $envEdition = strtolower((string) (getenv('AICONNECT_EDITION') ?: ''));
        $licensed   = \chgold\AIConnectPro\License\Validator::isValid()
            && \chgold\AIConnectPro\License\Validator::hasBundle('admin');

        if ($envEdition !== 'pro' && !$licensed) {
            return;
        }

        $module = new \chgold\AIConnectAdmin\Module\AdminModule($manifestService);
        $modules[$module->getModuleName()] = $module;
    }
}
