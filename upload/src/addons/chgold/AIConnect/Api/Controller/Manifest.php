<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;

class Manifest extends AbstractController
{
    public function actionGet()
    {
        $manifestService = \XF::service('chgold\AIConnect:Manifest');
        
        $coreModule = new \chgold\AIConnect\Module\CoreModule($manifestService);
        
        $manifest = $manifestService->generate();
        
        return $this->apiResult($manifest);
    }

    public function allowUnauthenticatedRequest($action)
    {
        // Manifest endpoint is public - no authentication required
        return true;
    }

    public function assertRequiredApiInput($keys)
    {
        return [];
    }
}
