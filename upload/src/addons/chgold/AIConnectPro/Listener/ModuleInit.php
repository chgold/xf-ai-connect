<?php

namespace chgold\AIConnectPro\Listener;

class ModuleInit
{
    public static function aiConnectModulesInit(array &$modules, \chgold\AIConnect\Service\Manifest $manifestService)
    {
        $module = new \chgold\AIConnectPro\Module\ProModule($manifestService);
        $modules[$module->getModuleName()] = $module;
    }
}
