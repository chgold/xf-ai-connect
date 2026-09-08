<?php

namespace chgold\AIConnectPro\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

/**
 * License check controller.
 *
 * Flow (matches XenForo's standard confirm-overlay convention):
 *   GET  aiconnect-pro/license/check  → renders a confirm form inside an overlay
 *   POST aiconnect-pro/license/check  → performs the remote validation, then
 *                                       redirects back to the Options page
 *
 * The "Check license now" button in the Options row is a plain link with
 * data-xf-click="overlay". XF fetches it via GET, shows the returned form in
 * an overlay, and the form's own submit does the POST. This is why the action
 * MUST answer GET as well — answering POST-only produced the
 * "This action is available via POST only" error.
 */
class License extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        // Legacy standalone page — kept so old bookmarks/routes still resolve.
        // Redirect to the canonical location (the Options group).
        return $this->redirect($this->getOptionsGroupLink());
    }

    /**
     * Link back to the license Options group.
     *
     * MUST pass the group id as the route's data segment, not as a query
     * param: buildLink('options/groups', null, ['group_id' => ...]) produces
     * the malformed "options/groups/&group_id=..." URL, which 404s.
     */
    protected function getOptionsGroupLink(): string
    {
        return $this->buildLink('options/groups', null) . 'aiconnect_pro_license/';
    }

    public function actionCheck(ParameterBag $params)
    {
        $validator = \chgold\AIConnectPro\License\Validator::class;

        if ($this->isPost()) {
            // The key itself is edited and saved on the Options page via XF's
            // normal save flow. This action only re-validates the SAVED key —
            // it deliberately does not accept a key, so there is exactly one
            // place to edit it and no risk of the two inputs disagreeing.
            $status = $validator::check(true);
            $label  = self::describeStatus($status);

            return $this->redirect($this->getOptionsGroupLink(), $label);
        }

        // GET → render a confirm step (no key input; see above).
        $viewParams = [
            'licenseKey' => $validator::getLicenseKey(),
            'status'     => $validator::getStatus(),
        ];
        return $this->view(
            'chgold\AIConnectPro:License\Check',
            'aiconnect_pro_license_check',
            $viewParams
        );
    }

    /**
     * Human-readable label for a validation verdict, surfaced as the redirect
     * flash message on the Options page.
     */
    public static function describeStatus(array $status): string
    {
        return match ($status['status'] ?? '') {
            'valid'            => 'License valid — updates active until ' . self::formatDate($status['updates_expire_at'] ?? null),
            'valid_no_updates' => 'License valid (perpetual) — updates expired ' . self::formatDate($status['updates_expire_at'] ?? null, '') . '. Renew for updates.',
            'invalid_domain'   => 'License is registered to a different domain: ' . ($status['licensed_domain'] ?? '?')
                                  . '. Change the licensed domain in your goldnat.ai account, then re-check.',
            'plugin_mismatch'  => 'This license belongs to a different product'
                                  . (($status['plugin_slug'] ?? '') !== '' ? ' (' . $status['plugin_slug'] . ')' : '')
                                  . ' and cannot enable AI Connect Pro for XenForo.',
            'invalid_key'      => 'License key not found. Check that the key matches your confirmation email exactly.',
            'suspended'        => 'License suspended. Contact support@gold-t.co.il.',
            'no_license'       => 'No license key entered.',
            'error_cached'     => 'Could not reach the license server — keeping the cached verdict, will retry automatically.',
            default            => 'Unknown response from the license server.',
        };
    }

    /**
     * Formats a licence-server ISO-8601 date (e.g. 2027-07-28T21:21:38.878Z)
     * using the board's own date format, so flash messages don't surface a raw
     * machine timestamp. Unparseable values are returned untouched.
     *
     * Plain text (not HTML) — this feeds a flash message, which XF escapes.
     */
    public static function formatDate($value, string $fallback = 'unknown'): string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return $fallback;
        }

        $ts = strtotime($raw);
        return $ts === false ? $raw : \XF::language()->date($ts);
    }

    protected function preDispatchController($action, ParameterBag $params): void
    {
        $this->assertAdminPermission('option');
    }
}
