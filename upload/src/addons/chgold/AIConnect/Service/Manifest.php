<?php

namespace chgold\AIConnect\Service;

use XF\Service\AbstractService;

class Manifest extends AbstractService
{
    protected $tools = [];

    /**
     * Declarative brief metrics — see docs-site/site-owners/manifest-format.md.
     *
     * Populated via registerBriefMetric() during module bootstrap and emitted as
     * `manifest.brief.metrics` so goldnat.ai can collect scalar values on a
     * schedule without a language model in the loop.
     *
     * @var array
     */
    protected $briefMetrics = [];

    /** @var array */
    protected $briefLists = [];

    /**
     * Register a tool
     */
    public function registerTool($name, $config)
    {
        if (empty($name) || empty($config['description']) || empty($config['input_schema'])) {
            return false;
        }

        $this->tools[$name] = [
            'name' => $name,
            'description' => $config['description'],
            'input_schema' => $config['input_schema'],
        ];

        return true;
    }

    /**
     * Register a declarative brief metric.
     *
     * Each entry names a TOOL, the ARGS to pass (with {{periodStart}},
     * {{periodEnd}} and {{timezone}} substituted per collection), and a
     * dotted VALUEPATH into the JSON response. The goldnat.ai collector
     * calls the tool and reads the scalar at valuePath — no LLM in the loop.
     *
     * Contract from the spec (manifest-format.md, brief section):
     *   - key          required. `^[a-z0-9]+(\.[a-z0-9_]+)+$`, 1–120 chars.
     *   - tool         required. Manifest tool name w/o site prefix, 1–200 chars.
     *   - args         optional. Constant object; supports {{...}} placeholders.
     *   - valuePath    required. Dotted, keys & numeric indices only (no wildcards).
     *   - unit         optional. Free-form ≤ 24 chars. ILS/USD/EUR/GBP → currency
     *                  formatting; anything else → plain suffix. Omit for pure counts.
     *   - granularity  optional. hour|day|week|month (default day).
     *
     * @return bool True on success, false if the entry was rejected.
     */
    public function registerBriefMetric(array $config): bool
    {
        if (empty($config['key']) || empty($config['tool']) || empty($config['valuePath'])) {
            return false;
        }
        if (!preg_match('/^[a-z0-9]+(\.[a-z0-9_]+)+$/', (string) $config['key'])) {
            return false;
        }
        if (count($this->briefMetrics) >= 50) {
            return false;
        }

        $metric = [
            'key'       => (string) $config['key'],
            'tool'      => (string) $config['tool'],
            'valuePath' => (string) $config['valuePath'],
        ];
        if (isset($config['args']) && is_array($config['args'])) {
            $metric['args'] = $config['args'];
        }
        if (isset($config['unit']) && is_string($config['unit']) && $config['unit'] !== '') {
            $metric['unit'] = substr($config['unit'], 0, 24);
        }
        if (
            isset($config['granularity'])
            && in_array($config['granularity'], ['hour', 'day', 'week', 'month'], true)
        ) {
            $metric['granularity'] = $config['granularity'];
        }

        // Later wins — allows extensions to override defaults for the same key.
        $this->briefMetrics[$metric['key']] = $metric;
        return true;
    }

    /**
     * Get all registered brief metrics as a list.
     */
    public function getBriefMetrics(): array
    {
        return array_values($this->briefMetrics);
    }

    /**
     * Get all registered tools
     */
    public function getTools()
    {
        return array_values($this->tools);
    }

    /**
     * Filter registered tools to only those the given visitor may access.
     *
     * Mirrors the 3-tier permission check in Api\Controller\Tools::checkToolPermission():
     *   Tier 1 – aiconnect.useTools        (master switch)
     *   Tier 2 – aiconnect.use_package_{id} (per-package, if module declares one)
     *   Tier 3 – aiconnect.tool_{mod}_{tool} (per-tool, only if permission is registered in DB)
     *
     * A single DB query fetches all registered permission IDs for the aiconnect group
     * to avoid N+1 queries when many tools are present.
     *
     * @param  array           $modules  Map of moduleName => ModuleBase instances
     * @param  \XF\Entity\User $visitor
     */
    public function filterAccessibleTools(array $modules, \XF\Entity\User $visitor): void
    {
        // Tier 1 — master switch: if denied, nothing is accessible
        if (!$visitor->hasPermission('aiconnect', 'useTools')) {
            $this->tools = [];
            return;
        }

        // Pre-fetch all registered permission IDs for this group (single query)
        $registeredPerms = \XF::db()->fetchPairs(
            'SELECT permission_id, permission_id FROM xf_permission WHERE permission_group_id = ?',
            ['aiconnect']
        );

        $filtered = [];

        foreach ($this->tools as $fullName => $toolDef) {
            $parts = explode('.', $fullName, 2);
            if (count($parts) !== 2) {
                $filtered[$fullName] = $toolDef;
                continue;
            }
            [$moduleName, $toolName] = $parts;

            // Tier 2 — package check (only if module declares a packageId).
            // Resolved PER TOOL: a module may spread its tools across several
            // packages (the Pro module gives each bundle its own master switch),
            // in which case testing one module-wide package would check a switch
            // that no longer governs the tool.
            $module = $modules[$moduleName] ?? null;
            if ($module !== null && method_exists($module, 'getPackageId')) {
                $packageId = method_exists($module, 'getPackageIdForTool')
                    ? $module->getPackageIdForTool($toolName)
                    : $module->getPackageId();
                if ($packageId !== null) {
                    $rawPkg  = 'use_package_' . $packageId;
                    $pkgPerm = strlen($rawPkg) <= 25 ? $rawPkg : substr($rawPkg, 0, 25);
                    if (!$visitor->hasPermission('aiconnect', $pkgPerm)) {
                        continue;
                    }
                }
            }

            // Tier 3 — per-tool check (only if the permission is registered)
            $permId = \chgold\AIConnect\Helper\Permission::toolPermId($moduleName, $toolName);
            if (isset($registeredPerms[$permId]) && !$visitor->hasPermission('aiconnect', $permId)) {
                continue;
            }

            $filtered[$fullName] = $toolDef;
        }

        $this->tools = $filtered;
    }

    /**
     * Generate WebMCP manifest
     */
    public function generate()
    {
        $baseUrl = \XF::options()->boardUrl;

        $addon = \XF::em()->find('XF:AddOn', 'chgold/AIConnect');
        $version = $addon ? $addon->version_string : '1.1.2';

        $manifest = [
            'schema_version' => '1.0',
            'name' => 'xenforo-ai-connect',
            'version' => $version,
            'description' => 'WebMCP bridge for XenForo - manage forum content and users',
            'api_version' => 'v1',
            'capabilities' => [
                'tools' => true,
                'resources' => false,
                'prompts' => false,
            ],
            'server' => [
                'url' => $baseUrl . '/api/ai-connect/v1',
                'description' => 'XenForo AI Connect API',
            ],
            'auth' => [
                'type' => 'oauth2',
                'flow' => 'authorization_code',
                'authorization_url' => $baseUrl . '/oauth.php',
                'token_url' => $baseUrl . '/api/aiconnect-oauth',
                'pkce_required' => true,
                'code_challenge_method' => 'S256',
                'redirect_uri' => 'urn:ietf:wg:oauth:2.0:oob',
                'scopes' => [
                    'read' => 'Read forum content and your profile',
                    'write' => 'Create and modify content',
                    'delete' => 'Delete content',
                    'admin' => 'Administrative access'
                ],
                'grant_types' => ['authorization_code', 'refresh_token'],
                'token_type' => 'Bearer'
            ],
            'usage' => [
                'tools_endpoint' => $baseUrl . '/api/aiconnect-tools',
                'method' => 'POST',
                'body_format' => 'mcp',
                'headers' => [
                    'Authorization' => 'Bearer {access_token}',
                    'Content-Type' => 'application/json',
                ],
            ],
        ];

        if (!empty($this->tools)) {
            $manifest['tools'] = $this->getTools();
        }

        // Declarative brief metrics — see manifest-format.md (brief section).
        // Only emitted when at least one entry was registered; invalid metrics
        // never reach here (registerBriefMetric already rejected them).
        if (!empty($this->briefMetrics) || !empty($this->briefLists)) {
            $brief = [];
            if (!empty($this->briefMetrics)) {
                $brief['metrics'] = $this->getBriefMetrics();
            }
            if (!empty($this->briefLists)) {
                $brief['lists'] = array_values($this->briefLists);
            }
            $manifest['brief'] = $brief;
        }

        // Expose the Pro plugin's advertised bundle keys so the license portal
        // and downstream tooling know what to offer. See BUNDLES-LICENSE-SPEC.md.
        // Kept as a static list here (single source of truth for XF Pro) since
        // the manifest is generated by the free addon on behalf of all modules.
        if (class_exists('\chgold\AIConnectPro\License\Validator', false)) {
            // 'core' is always loaded when ANY bundle is granted; it's listed
            // separately so consumers know the utility toolset (getMe, findUserByName)
            // exists as its own conceptual bundle even though it's not billable.
            $manifest['bundles_available'] = [
                'core',
                'moderation', 'writing', 'engagement', 'profile',
                'conversation', 'media', 'automation',
            ];
        }

        // Generate instructions that guide the AI agent on smart tool usage
        $toolNames = array_keys($this->tools);
        if (!empty($toolNames)) {
            $manifest['instructions'] = 'You are connected to a XenForo forum via AI Connect. '
                . 'This manifest is always available at: ' . $baseUrl . '/api/aiconnect-manifest '
                . "\n\n"
                . '## AVAILABLE TOOLS' . "\n"
                . 'Use ONLY the tools listed in the "tools" array. Do NOT claim capabilities beyond what is listed. '
                . 'Do NOT mention edition differences (Free/Pro).' . "\n\n"
                . '## SMART USAGE — ALWAYS TRY TO FULFILL THE REQUEST' . "\n"
                . 'Your goal is to ALWAYS return useful data to the user. Never give up because a parameter does not match exactly:' . "\n"
                . '- If a search returns no results → try with fewer filters (drop forum_id, relax date range, simplify search term).' . "\n"
                . '- If a date range is uncertain → use a wider range or omit the date filter entirely to return all history.' . "\n"
                . '- If the user asks for "recent" content without specifying a date → use since=today first, if empty try since=1week, then since=1month.' . "\n"
                . '- If the user asks for content from a vague period ("last summer", "a few months ago") → compute an approximate date_from/date_to and use it.' . "\n\n"
                . '## DATE FILTERING (searchThreads, searchPosts)' . "\n"
                . 'Parameter `since` accepts:' . "\n"
                . '  - Presets: today, yesterday, 1hour, 1week, 1month' . "\n"
                . '  - Dynamic: 3d (3 days), 6h (6 hours), 2w (2 weeks), 1y (1 year), 2years, 18months — any number + unit' . "\n"
                . '  - Specific date: 2026-03-15 (YYYY-MM-DD format)' . "\n"
                . '  - All history: "all" or "everything" → returns all records regardless of date' . "\n"
                . '  - Unknown value → falls back to all history (no date limit)' . "\n"
                . 'Parameter `until`: upper time bound, same format as `since`. Combine with `since` for a closed window: since=3w&until=2w = the week between 3 and 2 weeks ago.' . "\n"
                . 'Parameters `date_from` / `date_to`: Unix timestamp or "YYYY-MM-DD" string for any exact date range.' . "\n\n"
                . $this->getTranslationInstructions()
                . '## COMBINING TOOLS' . "\n"
                . '- To answer "who am I?" → use getCurrentUser.' . "\n"
                . '- To find a thread and then read its posts → searchThreads first, then searchPosts with thread_id.' . "\n"
                . '- To get the latest content → searchThreads or searchPosts with no filters (empty args {}).' . "\n"
                . '- To translate forum content → getThread/getPost first, then translate the content.';
        }

        // Expose registered OAuth clients so AI agents know which client_id to use
        $clients = \XF::db()->fetchPairs(
            'SELECT client_id, client_name FROM xf_ai_connect_oauth_clients ORDER BY client_name'
        );
        if (!empty($clients)) {
            $manifest['auth']['registered_clients'] = $clients;
        }

        return $manifest;
    }

    /**
     * Generate translation instructions based on the configured provider.
     */
    protected function getTranslationInstructions()
    {
        $provider = \XF::options()->aiconnect_translation_provider ?? 'ai_self';

        if ($provider === 'mymemory') {
            return '## TRANSLATION (translate)' . "\n"
                . 'Accepts text of ANY length — automatically split into chunks if needed. '
                . 'Pass the full text without worrying about length.' . "\n"
                . 'IMPORTANT: Translation uses the MyMemory free API, which is limited to ~5,000 characters/day. '
                . 'If you receive a quota_exceeded error, inform the user that the daily translation limit has been reached and suggest trying again tomorrow. '
                . 'Use translation sparingly — prefer translating only what the user specifically asks for, not entire threads.' . "\n\n";
        }

        if ($provider === 'ai_self') {
            return '## TRANSLATION' . "\n"
                . 'You have built-in translation capabilities. When the user asks you to translate content, '
                . 'translate it directly using your own language abilities — no external tool is needed. '
                . 'You can translate between any languages.' . "\n\n";
        }

        // disabled — no mention of translation
        return '';
    }

    /**
     * Generate manifest as JSON
     */
    public function generateJson($pretty = true)
    {
        $manifest = $this->generate();
        $options = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : JSON_UNESCAPED_SLASHES;
        return json_encode($manifest, $options);
    }
}
