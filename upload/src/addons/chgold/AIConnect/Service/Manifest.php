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
        
        $manifest = [
            'schema_version' => '1.0',
            'name' => 'xenforo-ai-connect',
            'version' => '1.0.0',
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
                'type' => 'bearer',
                'login_url' => $baseUrl . '/api/ai-connect/auth/login',
                'description' => 'Direct authentication with XenForo username and password',
                'method' => 'POST',
                'body' => [
                    'username' => 'XenForo username',
                    'password' => 'XenForo password',
                ],
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
