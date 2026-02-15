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

    protected function assertRequiredApiInput($keys)
    {
        return [];
    }
}
