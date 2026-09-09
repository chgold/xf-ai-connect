<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Admin — Add-ons bundle: list + enable + disable installed add-ons.
 *
 * All 3 require admin scope + is_admin + the addon admin permission.
 * Never uninstalls (that requires filesystem access + schema migration ops).
 * Enable/disable maps to XF native flip of xf_addon.active + rebuild caches.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminAddonsTrait
{
    protected function registerAddonsTools()
    {
        $this->registerTool('listAddons', [
            'description' => 'List all installed add-ons (id, title, version, enabled). No arguments.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('enableAddon', [
            'description' => 'Enable a currently-disabled add-on. Rebuilds caches automatically.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['addon_id'],
                'properties' => [
                    'addon_id' => ['type' => 'string', 'description' => 'Add-on ID (e.g. "chgold/AIConnect")'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('disableAddon', [
            'description' => 'Disable a currently-enabled add-on. Rebuilds caches. Refuses to disable our own AIConnect chain (would lock the API out).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['addon_id'],
                'properties' => [
                    'addon_id' => ['type' => 'string'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_listAddons($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('addOn')) return $err;

        // getAddOnsForList() signature varies across XF versions and can return
        // arrays instead of entities, causing property-access crashes.
        // Use the finder pattern directly (same fix that worked for styles).
        $addons = \XF::finder('XF:AddOn')->order('addon_id')->fetch();
        $out = [];
        foreach ($addons as $a) {
            $out[] = [
                'addon_id' => (string) $a->addon_id,
                'title' => (string) $a->title,
                'version_string' => (string) $a->version_string,
                'version_id' => (int) $a->version_id,
                'active' => (bool) $a->active,
                'is_processing' => (bool) $a->is_processing,
            ];
        }
        return $this->success(['addons' => $out, 'count' => count($out)]);
    }

    public function execute_enableAddon($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('addOn')) return $err;

        $addonId = (string) $params['addon_id'];
        $addon = \XF::em()->find('XF:AddOn', $addonId);
        if (!$addon) return $this->error('not_found', "Add-on '$addonId' not installed");

        if ($addon->active) {
            return $this->success([
                'addon_id' => $addonId,
                'already_enabled' => true,
                'active' => true,
            ]);
        }

        /** @var \XF\AddOn\AddOn $handler */
        $handler = \XF::app()->addOnManager()->getById($addonId);
        if (!$handler) return $this->error('not_found', "Add-on handler for '$addonId' missing");

        \XF::app()->addOnManager()->enable($handler);

        return $this->success([
            'addon_id' => $addonId,
            'enabled' => true,
            'active' => true,
        ]);
    }

    public function execute_disableAddon($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('addOn')) return $err;

        $addonId = (string) $params['addon_id'];

        // Guard against locking ourselves out
        $lockChain = ['chgold/AIConnect', 'chgold/AIConnectPro', 'chgold/AIConnectAdmin'];
        if (in_array($addonId, $lockChain, true)) {
            return $this->error(
                'no_permission',
                "Refusing to disable '$addonId' via API — this would lock the AI Connect API out of the site. "
                . 'Disable via ACP if intentional.'
            );
        }

        $addon = \XF::em()->find('XF:AddOn', $addonId);
        if (!$addon) return $this->error('not_found', "Add-on '$addonId' not installed");

        if (!$addon->active) {
            return $this->success([
                'addon_id' => $addonId,
                'already_disabled' => true,
                'active' => false,
            ]);
        }

        $handler = \XF::app()->addOnManager()->getById($addonId);
        if (!$handler) return $this->error('not_found', "Add-on handler for '$addonId' missing");

        \XF::app()->addOnManager()->disable($handler);

        return $this->success([
            'addon_id' => $addonId,
            'disabled' => true,
            'active' => false,
        ]);
    }
}
