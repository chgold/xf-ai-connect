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
        
        $authHeader = $this->request()->getServer('HTTP_AUTHORIZATION');
        if (!$authHeader || !preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            throw $this->exception($this->error('Missing or invalid Authorization header', 401));
        }
        
        $token = $matches[1];
        $authService = \XF::service('chgold\AIConnect:Auth');
        $validation = $authService->validateAccessToken($token);
        
        if (!$validation['valid']) {
            throw $this->exception($this->error('Invalid or expired token: ' . ($validation['error'] ?? 'Unknown error'), 401));
        }
        
        $userId = $validation['user_id'];
        $user = \XF::em()->find('XF:User', $userId);
        
        if (!$user) {
            throw $this->exception($this->error('User not found', 404));
        }
        
        \XF::setVisitor($user);
        
        $manifestService = \XF::service('chgold\AIConnect:Manifest');
        $this->coreModule = new \chgold\AIConnect\Module\CoreModule($manifestService);
    }

    public function actionPost(ParameterBag $params)
    {
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
