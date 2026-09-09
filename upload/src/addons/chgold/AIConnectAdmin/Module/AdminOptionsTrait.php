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

        /** @var \XF\Repository\OptionRepository $repo */
        $repo = \XF::em()->getRepository('XF:Option');
        $success = $repo->updateOptions([$key => $params['value']]);

        if (!$success) {
            return $this->error('validation_failed', 'Option update rejected by validator');
        }

        // reload
        $option = \XF::em()->find('XF:Option', $key, ['forceRefresh' => true]);

        return $this->success([
            'option_id' => $key,
            'option_value' => $option->option_value,
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
