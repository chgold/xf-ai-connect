<?php

namespace chgold\AIConnectPro\Listener;

class ModuleInit
{
    public static function aiConnectModulesInit(array &$modules, \chgold\AIConnect\Service\Manifest $manifestService)
    {
        // The Pro tool-set is only registered onto the core when Pro is licensed.
        // Gating is LICENCE-ONLY: a verified Pro licence from goldnat.ai is the sole
        // way the tools load. There is deliberately NO environment/config bypass — a
        // site cannot self-grant the paid tool-set. Validator::isValid() fail-opens
        // on 'error_cached' so a paying customer is never hard-blocked by a network
        // blip. Without a valid license the Pro tools simply never load. Dev/test
        // sites obtain a real Pro licence keyed to their domain, same as any customer.
        if (!\chgold\AIConnectPro\License\Validator::isValid()) {
            return;
        }

        $module = new \chgold\AIConnectPro\Module\ProModule($manifestService);
        $modules[$module->getModuleName()] = $module;
    }
}
