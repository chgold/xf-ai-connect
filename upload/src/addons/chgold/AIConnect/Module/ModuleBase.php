<?php

namespace chgold\AIConnect\Module;

abstract class ModuleBase
{
    protected $moduleName;
    protected $tools = [];
    protected $manifestService;

    /**
     * Optional: package this module belongs to (null = free tier).
     * Pro modules set this to their package ID, e.g. 'premium'.
     * The permission 'use_package_{packageId}' is checked before per-tool permissions.
     *
     * @var string|null
     */
    protected $packageId = null;

    public function __construct($manifestService)
    {
        $this->manifestService = $manifestService;
        $this->registerTools();
    }

    abstract protected function registerTools();

    protected function registerTool($name, $config)
    {
        if (!isset($config['description']) || !isset($config['input_schema'])) {
            return false;
        }

        $fullName = $this->moduleName . '.' . $name;

        $this->tools[$name] = [
            'name' => $fullName,
            'description' => $config['description'],
            'input_schema' => $config['input_schema'],
            'callback' => $config['callback'] ?? [$this, 'execute_' . $name],
        ];

        if ($this->manifestService) {
            $this->manifestService->registerTool($fullName, [
                'description' => $config['description'],
                'input_schema' => $config['input_schema'],
            ]);
        }

        return true;
    }

    public function executeTool($toolName, $params = [])
    {
        if (!isset($this->tools[$toolName])) {
            return $this->error('tool_not_found', sprintf('Tool %s not found', $toolName));
        }

        $tool = $this->tools[$toolName];

        $validated = $this->validateParams($params, $tool['input_schema']);
        if (isset($validated['error'])) {
            return $validated;
        }

        if (!is_callable($tool['callback'])) {
            return $this->error('tool_not_callable', sprintf('Tool %s is not callable', $toolName));
        }

        try {
            return call_user_func($tool['callback'], $validated);
        } catch (\Exception $e) {
            return $this->error('tool_execution_error', $e->getMessage());
        }
    }

    protected function validateParams($params, $schema)
    {
        if (!isset($schema['properties'])) {
            return $params;
        }

        $validated = [];
        $required = $schema['required'] ?? [];

        foreach ($schema['properties'] as $key => $prop) {
            $isRequired = in_array($key, $required);

            if ($isRequired && !isset($params[$key])) {
                return $this->error('missing_parameter', sprintf('Required parameter %s is missing', $key));
            }

            if (isset($params[$key])) {
                $value = $params[$key];

                if (isset($prop['type'])) {
                    $typeValid = $this->validateType($value, $prop['type']);
                    if (!$typeValid) {
                        return $this->error(
                            'invalid_type',
                            sprintf('Parameter %s must be of type %s', $key, $prop['type'])
                        );
                    }
                }

                $validated[$key] = $value;
            } elseif (isset($prop['default'])) {
                $validated[$key] = $prop['default'];
            }
        }

        return $validated;
    }

    protected function validateType($value, $type)
    {
        switch ($type) {
            case 'string':
                return is_string($value);
            case 'integer':
                return is_int($value) || (is_string($value) && ctype_digit($value));
            case 'number':
                return is_numeric($value);
            case 'boolean':
                return is_bool($value) || in_array($value, ['true', 'false', 0, 1], true);
            case 'array':
                return is_array($value);
            case 'object':
                return is_object($value) || is_array($value);
            default:
                return true;
        }
    }

    public function getTools()
    {
        return $this->tools;
    }

    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Returns an array of tool name => human-readable label pairs.
     * Used by Setup::syncToolPermissions() to register per-tool permissions.
     *
     * @return array<string, string>  ['toolName' => 'Human label', ...]
     */
    public function getToolNames(): array
    {
        $names = [];
        foreach (array_keys($this->tools) as $name) {
            // Convert camelCase to "Title Case" for the label
            $label = ucfirst(ltrim(preg_replace('/[A-Z]/', ' $0', $name)));
            $names[$name] = $label;
        }
        return $names;
    }

    /**
     * Returns the package ID for this module, or null if it is a free-tier module.
     *
     * @return string|null
     */
    public function getPackageId(): ?string
    {
        return $this->packageId;
    }

    /**
     * Returns prompt metadata for each tool in this module.
     *
     * Used by the token generator (InfoPage::buildPersonalizedPrompt) to produce
     * personalised, permission-filtered prompts.  Override this in every module
     * so that newly added tools are automatically reflected in the generated prompt
     * without touching InfoPage.
     *
     * Return format:
     *   [
     *     'toolName' => [
     *       'hint'       => 'Short usage note for the MCP section',
     *       'url_params' => [           // GET param strings for the fallback URL section
     *         'param1=VAL&param2=VAL',  // one string per example URL (empty string = no extra params)
     *       ],                          // empty array = POST-only tool, no URL examples
     *     ],
     *   ]
     *
     * Keys are short tool names (without the module prefix, e.g. 'searchThreads').
     *
     * @return array<string, array{hint: string, url_params: string[]}>
     */
    public function getToolPromptMeta(): array
    {
        return [];
    }

    protected function success($data, $message = null)
    {
        return [
            'success' => true,
            'data' => $data,
            'message' => $message,
        ];
    }

    protected function error($code, $message, $data = null)
    {
        return [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ],
        ];
    }
}
