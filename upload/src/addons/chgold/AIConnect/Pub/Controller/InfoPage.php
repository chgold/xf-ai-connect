<?php

namespace chgold\AIConnect\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class InfoPage extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        $visitor = \XF::visitor();

        if (!$visitor->hasPermission('aiconnect', 'viewAiConnect')) {
            return $this->noPermission();
        }

        $hasTools = $visitor->user_id && $visitor->hasPermission('aiconnect', 'useTools');

        // Anonymous users: toggle controls page access.
        // Logged-in users always reach the page — template shows the appropriate message.
        if (!$visitor->user_id && !\XF::options()->aiconnect_nav_top) {
            return $this->noPermission();
        }

        $options    = \XF::options();
        $request = $this->request();
        $scheme  = $request->getServer('HTTPS') === 'on' ? 'https' : 'http';
        // HTTP_HOST already contains host:port when a non-standard port is used
        $host    = $request->getServer('HTTP_HOST') ?: $request->getServer('SERVER_NAME');
        $baseUrl = rtrim($scheme . '://' . $host, '/');
        $forumTitle = $options->boardTitle ?? \XF::phrase('untitled');

        $manifestUrl       = $baseUrl . '/api/ai-connect/manifest';
        $authorizeUrl      = $baseUrl . '/oauth.php';
        $infoUrl           = 'https://ai-connect.gold-t.co.il/';

        $viewParams = [
            'forumTitle'    => $forumTitle,
            'baseUrl'       => $baseUrl,
            'manifestUrl'   => $manifestUrl,
            'authorizeUrl'  => $authorizeUrl,
            'infoUrl'       => $infoUrl,
        ];

        return $this->view(
            'chgold\AIConnect:InfoPage',
            'aiconnect_info_page',
            $viewParams
        );
    }

    public function actionGenerateToken(ParameterBag $params)
    {
        $this->assertPostOnly();

        $visitor = \XF::visitor();

        if (!$visitor->user_id) {
            return $this->error(\XF::phrase('you_must_be_logged_in_to_do_that'), 403);
        }

        if (!$visitor->hasPermission('aiconnect', 'useTools')) {
            return $this->error(\XF::phrase('do_not_have_permission'), 403);
        }

        /** @var \chgold\AIConnect\Service\OAuthServer $oauthServer */
        $oauthServer = $this->service('chgold\AIConnect:OAuthServer');

        $token = $oauthServer->createAccessToken(
            'claude-ai',
            $visitor->user_id,
            ['read', 'write']
        );

        // Build filtered manifest — only tools this user is permitted to call.
        /** @var \chgold\AIConnect\Service\Manifest $manifestService */
        $manifestService   = $this->service('chgold\AIConnect:Manifest');
        $coreModule        = new \chgold\AIConnect\Module\CoreModule($manifestService);
        $translationModule = new \chgold\AIConnect\Module\TranslationModule($manifestService);
        $modules = [
            $coreModule->getModuleName()        => $coreModule,
            $translationModule->getModuleName() => $translationModule,
        ];
        \XF::fire('ai_connect_modules_init', [&$modules, $manifestService], 'chgold/AIConnect');
        $manifestService->filterAccessibleTools($modules, $visitor);
        $manifest = $manifestService->generate();

        $accessibleTools = array_column($manifest['tools'] ?? [], 'name');

        // Resolve base URL from the current request so the prompt contains correct endpoints.
        $request = $this->request();
        $scheme  = $request->getServer('HTTPS') === 'on' ? 'https' : 'http';
        $host    = $request->getServer('HTTP_HOST') ?: $request->getServer('SERVER_NAME');
        $baseUrl = rtrim($scheme . '://' . $host, '/');

        $promptText = $this->buildPersonalizedPrompt($baseUrl, $token['access_token'], $accessibleTools, $modules);

        return $this->view(
            'chgold\AIConnect:InfoPage\GenerateToken',
            '',
            [
                'access_token' => $token['access_token'],
                'token_type'   => 'Bearer',
                'expires_in'   => $token['expires_in'],
                'prompt_text'  => $promptText,
            ]
        );
    }

    /**
     * Build the complete personalised prompt text that users paste into their AI agent.
     * Lists only the tools the given visitor is permitted to use.
     *
     * Tool metadata (hints, URL examples) is collected from each module's getToolPromptMeta().
     * Adding a new module automatically populates this prompt — no changes needed here.
     *
     * @param  string   $baseUrl         e.g. https://forum.example.com
     * @param  string   $accessToken     Bearer token just issued
     * @param  string[] $accessibleTools Filtered list of full tool names (e.g. ['xenforo.searchThreads', ...])
     * @param  array    $modules         Map of moduleName => ModuleBase instances
     * @return string
     */
    protected function buildPersonalizedPrompt(
        string $baseUrl,
        string $accessToken,
        array $accessibleTools,
        array $modules
    ): string {
        $options     = \XF::options();
        $hostname    = parse_url($baseUrl, PHP_URL_HOST) ?: 'forum';
        $siteKey     = preg_replace('/[^a-zA-Z0-9_-]/', '_', $hostname);
        $siteName    = $options->boardTitle ?? 'Forum';
        $toolUrl     = $baseUrl . '/api/aiconnect-tools';
        $manifestUrl = $baseUrl . '/api/aiconnect-manifest';

        // Collect prompt metadata from every module (generic — works for any future module).
        // Each module declares its own hints and URL param examples via getToolPromptMeta().
        $toolMeta = [];
        foreach ($modules as $moduleName => $module) {
            foreach ($module->getToolPromptMeta() as $toolName => $meta) {
                $fullName = $moduleName . '.' . $toolName;
                // Build full URLs from the param strings the module provided
                $urls = [];
                foreach ($meta['url_params'] as $paramStr) {
                    $url = $toolUrl . '?token=' . $accessToken . '&name=' . $fullName;
                    if ($paramStr !== '') {
                        $url .= '&' . $paramStr;
                    }
                    $urls[] = $url;
                }
                $toolMeta[$fullName] = [
                    'hint' => $meta['hint'],
                    'urls' => $urls,
                ];
            }
        }

        $lines = [];
        $lines[] = 'You have access to ' . $siteName . ' via AI Connect.';
        $lines[] = '';

        // --- MCP section ---
        $lines[] = '## MCP (Recommended — Claude Desktop)';
        $lines[] = 'Call webmcp_addSite with these parameters:';
        $lines[] = '  name: "' . $hostname . '"';
        $lines[] = '  manifest_url: "' . $manifestUrl . '"';
        $lines[] = '  token: "Bearer ' . $accessToken . '"';
        $lines[] = '';
        $lines[] = 'After adding the site, call these tools by EXACT name (do not search — use directly):';
        foreach ($accessibleTools as $toolName) {
            $mcpName = $siteKey . '_' . str_replace('.', '_', $toolName);
            $hint    = isset($toolMeta[$toolName]) ? $toolMeta[$toolName]['hint'] : '';
            $lines[] = '  ' . str_pad($mcpName, 50) . ($hint ? '<- ' . $hint : '');
        }

        $lines[] = '';
        $lines[] = '## Direct URL Access (Fallback)';
        $lines[] = 'If MCP is not available, use these GET endpoints (read-only tools):';
        $lines[] = '';

        $hasGetUrls = false;
        foreach ($accessibleTools as $toolName) {
            $meta = isset($toolMeta[$toolName]) ? $toolMeta[$toolName] : null;
            if ($meta && !empty($meta['urls'])) {
                foreach ($meta['urls'] as $url) {
                    $lines[] = '  ' . $url;
                }
                $hasGetUrls = true;
            }
        }

        if ($hasGetUrls) {
            $lines[] = '';
            $lines[] = 'Replace placeholders: KEYWORD=any word, THREAD_ID/POST_ID/FORUM_ID=numbers from URL';
            $lines[] = 'since= accepts: today | yesterday | 1hour | 1week | 1month | 3d | 6h | 2w | 1y | 2026-03-15 | all';
        }

        $lines[] = '';
        $lines[] = 'IMPORTANT: Do NOT use webmcp tool search — it may return tools from other sites.';
        $lines[] = 'Call the tools listed above by their EXACT full name. Start with getCurrentUser.';
        $lines[] = "\u26a0\ufe0f Security note: This token acts on behalf of the user who generated it. Handle it with care.";
        $lines[] = 'Documentation: https://ai-connect.gold-t.co.il/';

        return implode("\n", $lines);
    }

    public function allowUnauthenticatedAccess($action)
    {
        return strtolower($action) === 'index';
    }
}
