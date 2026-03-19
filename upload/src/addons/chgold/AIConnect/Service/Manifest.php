<?php

namespace chgold\AIConnect\Service;

use XF\Service\AbstractService;

class Manifest extends AbstractService
{
    protected $tools = [];

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
     * Get all registered tools
     */
    public function getTools()
    {
        return array_values($this->tools);
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
                'tools_endpoint' => $baseUrl . '/api/ai-connect/v1/tools/{tool_name}',
                'method' => 'POST',
                'headers' => [
                    'Authorization' => 'Bearer {access_token}',
                    'Content-Type' => 'application/json',
                ],
            ],
        ];

        if (!empty($this->tools)) {
            $manifest['tools'] = $this->getTools();
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
