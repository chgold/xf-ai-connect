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
        if ($rateLimiter->isRateLimited($identifier)['limited']) {
            return $this->error('Rate limit exceeded. Please slow down your requests.', 429);
        }
        $rateLimiter->recordRequest($identifier);

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

    protected static $readOnlyTools = [
        'xenforo' => ['searchThreads', 'getThread', 'searchPosts', 'getPost', 'getCurrentUser'],
        'translation' => ['getSupportedLanguages'],
    ];

    public function actionGet(ParameterBag $params)
    {
        $visitor = \XF::visitor();
        $rateLimiter = \XF::service('chgold\AIConnect:RateLimiter');
        $getIdentifier = 'user_' . $visitor->user_id;
        if ($rateLimiter->isRateLimited($getIdentifier)['limited']) {
            return $this->error('Rate limit exceeded. Please slow down your requests.', 429);
        }
        $rateLimiter->recordRequest($getIdentifier);

        $toolName = $this->request()->filter('name', 'str') ?: $params->tool_name;
        $argsRaw  = $this->request()->filter('args', 'str');

        if ($argsRaw) {
            $input = @json_decode($argsRaw, true) ?: [];
        } else {
            $input = $this->request()->filter([
                'search'    => 'str',
                'thread_id' => 'uint',
                'post_id'   => 'uint',
                'forum_id'  => 'uint',
                'username'  => 'str',
                'user_id'   => 'uint',
                'limit'     => 'uint',
                'since'     => 'str',
                'until'     => 'str',
                'date_from' => 'str',
                'date_to'   => 'str',
            ]);
            $input = array_filter($input, function ($v) {
                return $v !== '' && $v !== 0 && $v !== null;
            });
        }

        if (!$toolName) {
            return $this->error('Tool name is required (use ?name=module.toolName)', 400);
        }

        list($moduleName, $tool) = $this->parseToolName($toolName);

        $allowedTools = self::$readOnlyTools[$moduleName] ?? [];
        if (!in_array($tool, $allowedTools, true)) {
            return $this->error('Write operations require POST. Use POST /api/aiconnect-tools for: ' . $tool, 405);
        }

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
