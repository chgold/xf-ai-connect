<?php

namespace chgold\AIConnectAdmin\Listener;

class ModuleInit
{
    /**
     * Registers the administrative tool-set onto the core.
     *
     * Admin is a STANDALONE product: it depends only on the free Core add-on and
     * validates its OWN licence (xenforo-addon-admin) independently of Pro.
     * Precedence: AICONNECT_EDITION dev override ('admin'/'pro'/'all', for dev/test
     * sites without a licence) -> a verified Admin licence. Validator::isValid()
     * fail-opens on 'error_cached' so a paying customer is never hard-blocked by a
     * network blip. Without a valid Admin licence the tools never load — they do
     * not appear in the manifest at all (fail-closed, exactly like Pro).
     */
    public static function aiConnectModulesInit(array &$modules, \chgold\AIConnect\Service\Manifest $manifestService)
    {
        $envEdition = strtolower((string) (getenv('AICONNECT_EDITION') ?: ''));
        if (!in_array($envEdition, ['admin', 'pro', 'all'], true)
            && !\chgold\AIConnectAdmin\License\Validator::isValid()
        ) {
            return;
        }

        $module = new \chgold\AIConnectAdmin\Module\AdminModule($manifestService);
        $modules[$module->getModuleName()] = $module;
    }
}
