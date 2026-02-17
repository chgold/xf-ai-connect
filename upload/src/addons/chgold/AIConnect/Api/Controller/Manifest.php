<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;

class Manifest extends AbstractController
{
    public function actionGet()
    {
        $manifestService = \XF::service('chgold\AIConnect:Manifest');

        $coreModule = new \chgold\AIConnect\Module\CoreModule($manifestService);
        $modules = [$coreModule->getModuleName() => $coreModule];

        \XF::fire('ai_connect_modules_init', [&$modules, $manifestService], 'chgold/AIConnect');

        $manifest = $manifestService->generate();

        return $this->apiResult($manifest);
    }

    public function allowUnauthenticatedRequest($action)
    {
        return true;
    }

    public function assertRequiredApiInput($keys)
    {
        return [];
    }
}
