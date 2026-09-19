<?php

namespace chgold\AIConnectAdmin\Listener;

class ModuleInit
{
    /**
     * Registers the administrative tool-set onto the core.
     *
     * Admin is a STANDALONE product: it depends only on the free Core add-on and
     * validates its OWN licence (xenforo-addon-admin) independently of Pro.
     *
     * Gating is LICENCE-ONLY: a verified Admin licence from goldnat.ai is the sole
     * way the tools load. There is deliberately NO environment/config bypass — a
     * site cannot self-grant the paid tool-set. Validator::isValid() fail-opens on
     * 'error_cached' so a paying customer is never hard-blocked by a network blip.
     * Without a valid Admin licence the tools never load — they do not appear in
     * the manifest at all (fail-closed, exactly like Pro). Dev/test sites obtain a
     * real Admin licence keyed to their domain, same as any customer.
     */
    public static function aiConnectModulesInit(array &$modules, \chgold\AIConnect\Service\Manifest $manifestService)
    {
        if (!\chgold\AIConnectAdmin\License\Validator::isValid()) {
            return;
        }

        $module = new \chgold\AIConnectAdmin\Module\AdminModule($manifestService);
        $modules[$module->getModuleName()] = $module;
    }
}
