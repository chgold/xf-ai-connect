<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Tools extends AbstractController
{
    protected $modules = [];

    public function allowUnauthenticatedRequest($action)
    {
        return false;
    }

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

        if (!$this->checkToolPermission($visitor, $moduleName, $tool)) {
            return $this->error('You do not have permission to use this tool.', 403);
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

        if (!$this->checkToolPermission($visitor, $moduleName, $tool)) {
            return $this->error('You do not have permission to use this tool.', 403);
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

    /**
     * Checks whether the current visitor may execute a tool.
     *
     * Three-tier check:
     *  1. Global master switch: aiconnect.useTools must be Allow.
     *  2. Package switch (optional): if the module declares a packageId, the
     *     corresponding aiconnect.use_package_{id} permission must be Allow.
     *  3. Per-tool: aiconnect.tool_{module}_{tool} must be Allow (if registered).
     *
     * @param  \XF\Entity\User $visitor
     * @param  string          $moduleName
     * @param  string          $toolName
     * @return bool
     */
    protected function checkToolPermission($visitor, string $moduleName, string $toolName): bool
    {
        // 1. Master switch
        if (!$visitor->hasPermission('aiconnect', 'useTools')) {
            return false;
        }

        // 2. Package switch (only if the module belongs to a package)
        $module = $this->modules[$moduleName] ?? null;
        if ($module !== null && method_exists($module, 'getPackageId')) {
            $packageId = $module->getPackageId();
            if ($packageId !== null) {
                $rawPkg = 'use_package_' . $packageId;
                $pkgPerm = strlen($rawPkg) <= 25 ? $rawPkg : substr($rawPkg, 0, 25);
                if (!$visitor->hasPermission('aiconnect', $pkgPerm)) {
                    return false;
                }
            }
        }

        // 3. Per-tool permission (only if the permission is registered in xf_permission)
        $rawPermId = 'tool_' . $moduleName . '_' . $toolName;
        $permId    = strlen($rawPermId) <= 25 ? $rawPermId : substr($rawPermId, 0, 25);

        $exists = \XF::db()->fetchOne(
            'SELECT permission_id FROM xf_permission
             WHERE permission_group_id = ? AND permission_id = ?',
            ['aiconnect', $permId]
        );

        if ($exists) {
            return $visitor->hasPermission('aiconnect', $permId);
        }

        // Permission not yet registered (dynamic/new tool) — global + package checks passed
        return true;
    }

    public function assertRequiredApiInput($keys)
    {
        return [];
    }
}
