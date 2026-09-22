<?php

namespace chgold\AIConnectModeration\Listener;

class ModuleInit
{
    /**
     * Registers the moderation tool-set onto the core.
     *
     * Moderation is a STANDALONE product: it depends only on the free Core add-on and
     * validates its OWN licence (xenforo-moderation-pro) independently of Pro and Admin.
     *
     * Gating is LICENCE-ONLY: a verified Moderation licence from goldnat.ai is the sole
     * way the tools load. There is deliberately NO environment/config bypass — a
     * site cannot self-grant the paid tool-set. Validator::isValid() fail-opens on
     * 'error_cached' so a paying customer is never hard-blocked by a network blip.
     * Without a valid Moderation licence the tools never load — they do not appear in
     * the manifest at all (fail-closed, exactly like Admin). Dev/test sites obtain a
     * real Moderation licence keyed to their domain, same as any customer.
     */
    public static function aiConnectModulesInit(array &$modules, \chgold\AIConnect\Service\Manifest $manifestService)
    {
        if (!\chgold\AIConnectModeration\License\Validator::isValid()) {
            return;
        }

        $module = new \chgold\AIConnectModeration\Module\ModerationModule($manifestService);
        $modules[$module->getModuleName()] = $module;
    }
}
