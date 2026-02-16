<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Tools extends AbstractController
{
    protected $coreModule;

    protected function preDispatchController($action, ParameterBag $params)
    {
        parent::preDispatchController($action, $params);
        
        $manifestService = \XF::service('chgold\AIConnect:Manifest');
        $this->coreModule = new \chgold\AIConnect\Module\CoreModule($manifestService);
    }

    public function actionPost(ParameterBag $params)
    {
        $toolName = $params->tool_name;
        $input = $this->request()->getInputForApi();

        if (!$toolName) {
            return $this->error('Tool name is required', 400);
        }

        list($module, $tool) = $this->parseToolName($toolName);

        if ($module !== 'xenforo') {
            return $this->error('Invalid module: ' . $module, 404);
        }

        $result = $this->coreModule->executeTool($tool, $input);

        if (isset($result['success']) && $result['success'] === false) {
            return $this->error(
                $result['error']['message'] ?? 'Tool execution failed',
                $result['error']['code'] === 'not_found' ? 404 : 400
            );
        }

        return $this->apiSuccess($result);
    }

    protected function parseToolName($fullName)
    {
        $parts = explode('.', $fullName, 2);
        
        if (count($parts) === 2) {
            return $parts;
        }
        
        return ['xenforo', $fullName];
    }

    public function assertRequiredApiInput($keys)
    {
        return [];
    }
}
