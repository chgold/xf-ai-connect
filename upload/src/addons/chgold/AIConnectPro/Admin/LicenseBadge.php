<?php

namespace chgold\AIConnectPro\Admin;

use chgold\AIConnectPro\License\Validator;

/**
 * Renders the "License required" label next to this add-on's row in the
 * Admin CP installed add-ons list (admin.php?add-ons/).
 *
 * Mirrors XF core's own convention for surfacing add-on problems in that
 * list — the red `label label--red` used for "Legacy add-on" / "Missing
 * files" — so the state is impossible to miss and reads like a native
 * XenForo indicator.
 *
 * Injected via an admin template modification on addon_list_macros using
 * <xf:callback> (method name must pass Php::nameIndicatesReadOnly, hence
 * the render* prefix). Shows ONLY when the module gate would actually keep
 * the Pro tools from loading, i.e. the exact same condition as
 * Listener\ModuleInit: no AICONNECT_EDITION=pro env override AND
 * Validator::isValid() false. Reads the cached verdict only — never
 * triggers a licence-server HTTP call on an admin page view.
 *
 * @param string                  $contents  Tag children (unused)
 * @param array                   $params    ['addOn' => \XF\AddOn\AddOn]
 * @param \XF\Template\Templater  $templater
 * @return string
 */
class LicenseBadge
{
    public static function renderBadge($contents, array $params, \XF\Template\Templater $templater): string
    {
        $addOn = $params['addOn'] ?? null;
        if (!$addOn || !is_callable([$addOn, 'getAddOnId'])) {
            return '';
        }
        if ($addOn->getAddOnId() !== 'chgold/AIConnectPro') {
            return '';
        }

        $envEdition = strtolower((string) (getenv('AICONNECT_EDITION') ?: ''));
        if ($envEdition === 'pro' || Validator::isValid()) {
            return '';
        }

        return $templater->renderTemplate('admin:aiconnect_pro_license_badge', [
            'licenseUrl' => Validator::ACCOUNT_URL,
        ]);
    }
}
