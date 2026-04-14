<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;

class Manifest extends AbstractController
{
    public function actionGet()
    {
        $manifestService = \XF::service('chgold\AIConnect:Manifest');

        $coreModule        = new \chgold\AIConnect\Module\CoreModule($manifestService);
        $translationModule = new \chgold\AIConnect\Module\TranslationModule($manifestService);

        $modules = [
            $coreModule->getModuleName()        => $coreModule,
            $translationModule->getModuleName() => $translationModule,
        ];

        \XF::fire('ai_connect_modules_init', [&$modules, $manifestService], 'chgold/AIConnect');

        // When the request carries a valid Bearer token, personalise the manifest:
        // show only the tools this specific user is allowed to call.
        // Anonymous requests (discovery) receive the full tool list.
        $visitor = \XF::visitor();
        if ($visitor->user_id) {
            $manifestService->filterAccessibleTools($modules, $visitor);
        }

        $manifest = $manifestService->generate();

        return $this->apiResult($manifest);
    }

    public function actionGetIndex()
    {
        return $this->actionGet();
    }

    public function actionGetManifest()
    {
        return $this->actionGet();
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
