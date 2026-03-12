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
                'pkce' => [
                    'required' => true,
                    'method' => 'S256'
                ],
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

        return $manifest;
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
