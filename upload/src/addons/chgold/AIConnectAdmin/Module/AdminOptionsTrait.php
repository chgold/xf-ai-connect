<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Admin — Options bundle: read + write board options.
 *
 * 2 tools (getOption + setOption). Both require admin scope + is_admin +
 * XF options admin permission. Setting an option persists via XF's OptionRepository
 * so cache invalidation is handled correctly.
 *
 * Refuses to touch options in a small blocklist that would lock the site
 * (boardActive=false, HTTPS-related config that could brick auth).
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminOptionsTrait
{
    /** Options where a wrong value would lock the site — refuse to set them via API. */
    private static array $optionSetBlocklist = [
        'boardActive',       // false = site closed
        'boardUrl',          // wrong = auth callbacks break
        'homePageUrl',       // wrong = redirect loop
    ];

    protected function registerOptionsTools()
    {
        $this->registerTool('getOption', [
            'description' => 'Read a board option by its option_id. Returns the current value + option_value_type.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['option_id'],
                'properties' => [
                    'option_id' => ['type' => 'string', 'description' => 'Option key (e.g. "boardTitle", "defaultStyleId")'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('setOption', [
            'description' => 'Set a board option value. Refuses a blocklist of site-locking keys. '
                . 'Uses XF OptionRepository to persist + invalidate cache correctly.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['option_id', 'value'],
                'properties' => [
                    'option_id' => ['type' => 'string'],
                    'value' => ['description' => 'Any scalar (string/int/bool) or array/object; XF stores per option_value_type'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_getOption($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertOptionsPermission()) return $err;

        $key = (string) $params['option_id'];
        $option = \XF::em()->find('XF:Option', $key);
        if (!$option) return $this->error('not_found', "Option '$key' not defined");

        return $this->success([
            'option_id' => $key,
            'option_value' => $option->option_value,
            'option_value_type' => $option->data_type,
            'default_value' => $option->default_value,
        ]);
    }

    public function execute_setOption($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertOptionsPermission()) return $err;

        $key = (string) $params['option_id'];
        if (in_array($key, self::$optionSetBlocklist, true)) {
            return $this->error(
                'no_permission',
                "Refusing to set '$key' via API — site-locking risk. Use ACP directly if intentional."
            );
        }

        $option = \XF::em()->find('XF:Option', $key);
        if (!$option) return $this->error('not_found', "Option '$key' not defined");

        // v1.4.13 defensive input normalization. Agent may pass value as an
        // already-JSON-encoded string (leading '{', '[', or '"') — happens
        // when a wrapper double-serializes or the agent copies a raw JSON
        // response as input. XF's Option::castOptionValue throws
        // "Only arrays can be set to array type options" if data_type=array
        // and the incoming value isn't a PHP array. Same defensive pattern as
        // setStyleProperty v1.4.10 (see AdminAppearanceTrait::unwrapNestedJsonStrings).
        $value = $params['value'];
        $dataType = (string) $option->data_type;
        if (is_string($value) && $value !== '') {
            $first = $value[0];
            if ($first === '{' || $first === '[' || $first === '"') {
                for ($i = 0; $i < 3; $i++) {
                    $decoded = json_decode($value, true);
                    if ($decoded === null && strtolower(trim($value)) !== 'null') break;
                    // For array-typed options accept only array; for others accept string/array/null.
                    if ($dataType === 'array' && !is_array($decoded)) break;
                    if (!is_string($decoded) && !is_array($decoded) && $decoded !== null) break;
                    $value = $decoded;
                    if (!is_string($value)) break;
                    $first = $value === '' ? '' : $value[0];
                    if ($first !== '{' && $first !== '[' && $first !== '"') break;
                }
            }
        }

        // Explicit type-guard BEFORE handing to XF, so the caller gets an
        // actionable error (shape hint) instead of the raw LogicException.
        if ($dataType === 'array' && !is_array($value)) {
            $currentShape = is_array($option->option_value)
                ? array_keys($option->option_value)
                : [];
            return $this->error(
                'validation_failed',
                "Option '$key' (data_type=array) requires an OBJECT value; got " . gettype($value) . '. '
                . 'Expected shape: ' . json_encode(
                    array_fill_keys($currentShape ?: ['key1', 'key2'], '...'),
                    JSON_UNESCAPED_UNICODE
                )
                . '. Current value: ' . json_encode($option->option_value, JSON_UNESCAPED_UNICODE)
            );
        }

        /** @var \XF\Repository\OptionRepository $repo */
        $repo = \XF::em()->getRepository('XF:Option');
        $success = $repo->updateOptions([$key => $value]);

        if (!$success) {
            return $this->error('validation_failed', 'Option update rejected by validator');
        }

        // reload
        $option = \XF::em()->find('XF:Option', $key, ['forceRefresh' => true]);

        return $this->success([
            'option_id' => $key,
            'option_value' => $option->option_value,
            'stored_type' => is_array($option->option_value) ? 'array' : gettype($option->option_value),
            'data_type' => $dataType,
            'updated' => true,
        ]);
    }

    private function assertOptionsPermission(): ?array
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasAdminPermission('option')) {
            return $this->error('no_permission', 'The "option" admin permission is required');
        }
        return null;
    }
}
