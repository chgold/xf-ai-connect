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

                // Check minLength for string params (trim-based: whitespace-only strings are rejected)
                if (is_string($value) && isset($prop['minLength']) && mb_strlen(trim($value)) < (int) $prop['minLength']) {
                    return $this->error(
                        'invalid_param',
                        sprintf('Parameter %s must be at least %d character(s)', $key, (int) $prop['minLength'])
                    );
                }
                // Check maxLength for string params
                if (is_string($value) && isset($prop['maxLength']) && mb_strlen($value) > (int) $prop['maxLength']) {
                    return $this->error(
                        'invalid_param',
                        sprintf('Parameter %s must be at most %d character(s)', $key, (int) $prop['maxLength'])
                    );
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
     * Package ID governing a SPECIFIC tool, or null when the tool is ungated.
     *
     * Modules whose tools are split across several packages (e.g. the Pro
     * module, whose tools belong to one bundle each) override this so the
     * manifest checks the master switch that actually owns the tool. The
     * default keeps the previous whole-module behaviour, so existing modules
     * are unaffected.
     *
     * @param string $toolName Tool name without the module prefix.
     * @return string|null
     */
    public function getPackageIdForTool(string $toolName): ?string
    {
        return $this->getPackageId();
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

    /**
     * Builds a one-line parameter hint for a tool straight from its input
     * schema, for the connection prompt.
     *
     * getToolPromptMeta() is a hand-written table, so it only ever covered the
     * handful of tools someone remembered to add — every tool registered since
     * appeared in the prompt as a bare name with no indication that it takes
     * arguments at all. The schema is already the single source of truth (it is
     * what the manifest publishes), so deriving the hint from it means a new
     * tool is documented the moment it is registered, and the two can never
     * drift apart again.
     *
     * Required parameters are listed first, since those are what a caller must
     * supply; optional ones follow, capped so the prompt stays readable.
     *
     * @param string $toolName short name, e.g. 'searchThreads'
     */
    public function buildSchemaHint(string $toolName, int $maxOptional = 3): string
    {
        $tool = $this->tools[$toolName] ?? null;
        if (!$tool) {
            return '';
        }

        $schema     = $tool['input_schema'] ?? [];
        $properties = $schema['properties'] ?? [];
        if (!is_array($properties) || !$properties) {
            return 'no arguments';
        }

        $required = array_flip((array) ($schema['required'] ?? []));
        $req      = [];
        $opt      = [];

        foreach ($properties as $name => $spec) {
            $type = is_array($spec) ? ($spec['type'] ?? 'string') : 'string';
            $part = $name . '=' . $type;

            if (isset($required[$name])) {
                $req[] = $part;
            } else {
                if (is_array($spec) && isset($spec['default'])) {
                    $default = $spec['default'];
                    $part .= ' (default ' . (is_bool($default)
                        ? ($default ? 'true' : 'false')
                        : (string) $default) . ')';
                }
                $opt[] = $part;
            }
        }

        $parts = $req;
        if ($req) {
            $parts[count($parts) - 1] .= ' [required]';
        }

        $shown = array_slice($opt, 0, $maxOptional);
        $parts = array_merge($parts, $shown);

        $hint = implode(', ', $parts);
        if (count($opt) > count($shown)) {
            $hint .= ', +' . (count($opt) - count($shown)) . ' more';
        }

        return $hint;
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
