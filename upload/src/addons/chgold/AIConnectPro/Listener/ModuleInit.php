<?php

namespace chgold\AIConnectPro\Listener;

class ModuleInit
{
    public static function aiConnectModulesInit(array &$modules, \chgold\AIConnect\Service\Manifest $manifestService)
    {
        // The Pro tool-set is only registered onto the core when Pro is licensed.
        // Precedence: AICONNECT_EDITION=pro env override (dev/test sites that have
        // no license) -> a verified license. Validator::isValid() also fail-opens
        // on 'error_cached' so a paying customer is never hard-blocked by a network
        // blip. Without a valid license the Pro tools simply never load.
        $envEdition = strtolower((string)(getenv('AICONNECT_EDITION') ?: ''));
        if ($envEdition !== 'pro' && !\chgold\AIConnectPro\License\Validator::isValid()) {
            return;
        }

        $module = new \chgold\AIConnectPro\Module\ProModule($manifestService);
        $modules[$module->getModuleName()] = $module;
    }
}
