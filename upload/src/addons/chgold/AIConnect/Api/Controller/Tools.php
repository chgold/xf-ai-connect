<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Tools extends AbstractController
{
    protected $modules = [];

    protected function preDispatchController($action, ParameterBag $params)
    {
        parent::preDispatchController($action, $params);

        $manifestService = \XF::service('chgold\AIConnect:Manifest');
        $coreModule = new \chgold\AIConnect\Module\CoreModule($manifestService);
        $translationModule = new \chgold\AIConnect\Module\TranslationModule($manifestService);

        $this->modules[$coreModule->getModuleName()] = $coreModule;
        $this->modules[$translationModule->getModuleName()] = $translationModule;

        \XF::fire('ai_connect_modules_init', [&$this->modules, $manifestService], 'chgold/AIConnect');
    }

    public function actionPost(ParameterBag $params)
    {
        $visitor = \XF::visitor();
        $rateLimiter = \XF::service('chgold\AIConnect:RateLimiter');
        $identifier = 'user_' . $visitor->user_id;
        if ($rateLimiter->isRateLimited($identifier)) {
            return $this->error('Rate limit exceeded. Please slow down your requests.', 429);
        }

        $rawInput = $this->request()->getInputRaw();
        $requestData = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error('Invalid JSON input', 400);
        }

        $toolName = $requestData['name'] ?? $params->tool_name;
        $input = $requestData['arguments'] ?? [];

        if (!$toolName) {
            return $this->error('Tool name is required', 400);
        }

        list($moduleName, $tool) = $this->parseToolName($toolName);

        if (!isset($this->modules[$moduleName])) {
            return $this->error('Invalid module: ' . $moduleName, 404);
        }

        $result = $this->modules[$moduleName]->executeTool($tool, $input);

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
