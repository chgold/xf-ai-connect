<?php

namespace chgold\AIConnectPro\Option;

use XF\Entity\Option;
use XF\Option\AbstractOption;

/**
 * Callback renderer for the aiconnect_pro_license_key option.
 *
 * Renders inside the Options page (Setup → Options → AI Connect Pro License)
 * so the admin never has to leave the standard XF options UI. Shows:
 *   - Current status badge (valid / invalid / no_license / error)
 *   - The license key textbox (saved via the standard XF options save flow)
 *   - "Check license now" button (fires JS → /admin/aiconnect-pro/license/check)
 *   - "Manage subscription" button → Paddle customer portal (opens in new tab)
 *
 * Save flow:
 *   1. Admin pastes key + clicks XF's "Save" button at the bottom of the page
 *   2. XF persists the key to xf_option.aiconnect_pro_license_key via the
 *      normal save pipeline (no custom controller needed for save)
 *   3. Admin clicks "Check license now" → AJAX POST to the check endpoint,
 *      which calls Validator::check(true), updates the status cache, and
 *      refreshes the badge inline
 *
 * Paddle flow:
 *   - When license is valid AND status.paddle_customer_portal_url is present,
 *     we render a "Manage subscription" button that opens that URL
 *   - When invalid/absent, the button is hidden (no dead link)
 */
class LicenseKey extends AbstractOption
{
    private const SUPPORT_EMAIL = 'support@gold-t.co.il';

    /**
     * Host of this installation — the value sent as `domain` on every
     * validation call, so showing it makes a mismatch self-explanatory.
     */
    private static function currentDomain(): string
    {
        $url = \XF::options()->boardUrl ?? '';
        return strtolower((string) (parse_url($url, PHP_URL_HOST) ?: '?'));
    }

    /**
     * Link to the goldnat.ai account area.
     *
     * License administration (including moving a license to another domain)
     * happens ONLY in the goldnat.ai account — never from inside this add-on.
     * A site must not be able to reassign a license to itself, so all we do
     * here is point the admin at the right place.
     */
    private static function accountLink(string $label): string
    {
        return '<a href="' . \chgold\AIConnectPro\License\Validator::ACCOUNT_URL . '"'
             . ' target="_blank" rel="noopener">' . $label . ' →</a>';
    }

    public static function renderOption(Option $option, array $htmlParams): string
    {
        $status  = \chgold\AIConnectPro\License\Validator::getStatus();
        $key     = (string) $option->option_value;

        $badge  = self::renderBadge($status, $key);
        $portal = self::renderPortalButton($status);

        // The re-check button is only meaningful once a key has been SAVED —
        // the check action validates the stored key, it does not accept one.
        // Showing it on an empty field invited the "why is there a second
        // input?" confusion, so it is hidden until there is something to check.
        $checkBtn = $key !== '' ? self::renderCheckButton() : '';

        $steps = $key === ''
            ? '<small><strong>Step 1:</strong> paste your key above. '
              . '<strong>Step 2:</strong> click <em>Save</em> at the bottom of this page. '
              . '<strong>Step 3:</strong> a <em>Check license now</em> button will appear here.</small>'
            : '<small>Edit the key above and click <em>Save</em> to change it, '
              . 'or use <em>Check license now</em> to re-validate the saved key.</small>';

        // Always offer the account link: moving a license to another domain,
        // viewing invoices and managing the subscription all live there, not
        // in this add-on.
        $account = '<br><small>' . self::accountLink('Manage this license on goldnat.ai')
                 . ' — change the licensed domain, view invoices, manage your subscription.</small>';

        return self::getTextboxRow($option, array_merge($htmlParams, [
            'inputType'   => 'text',
            'explainHtml' => $badge . '<br>' . $portal . $checkBtn
                . '<br><small>Format: <code>XFP-XXXX-XXXX-XXXX-XXXX</code></small><br>'
                . $steps . $account,
        ]));
    }

    private static function renderBadge(array $status, string $key): string
    {
        // No key entered — neutral message, no colour spam.
        if ($key === '') {
            return '<span class="badge badge--secondary">No license key entered</span>';
        }

        $s = $status['status'] ?? '';
        return match ($s) {
            'valid'           => '<span class="u-flexNoShrink" style="color:#2e7d32;font-weight:bold">✅ Active</span>'
                                  . ' — updates valid until '
                                  . self::formatLicenseDate($status['updates_expire_at'] ?? null),
            'valid_no_updates'=> '<span class="u-flexNoShrink" style="color:#f57c00;font-weight:bold">✅ Perpetual</span>'
                                  . ' — updates expired '
                                  . self::formatLicenseDate($status['updates_expire_at'] ?? null, '')
                                  . '. <a href="mailto:' . self::SUPPORT_EMAIL . '?subject=Renew%20AI%20Connect%20Pro%20updates">'
                                  . 'Contact us to renew</a>',
            // Domain transfer has no self-service endpoint yet, so point the
            // admin at support with the two facts we already know (the key's
            // registered domain and this site's domain) pre-filled.
            'invalid_domain'  => '<span style="color:#c62828;font-weight:bold">❌ Domain mismatch</span>'
                                  . ' — key is registered to <code>'
                                  . htmlspecialchars((string) ($status['licensed_domain'] ?? '?')) . '</code>,'
                                  . ' but this site is <code>' . htmlspecialchars(self::currentDomain()) . '</code>.'
                                  . '<br>' . self::accountLink('Change the licensed domain in your account')
                                  . ', then click "Check license now".',
            'plugin_mismatch' => '<span style="color:#c62828;font-weight:bold">❌ Wrong product</span>'
                                  . ' — this key belongs to '
                                  . (($status['plugin_slug'] ?? '') !== ''
                                      ? '<code>' . htmlspecialchars((string) $status['plugin_slug']) . '</code>'
                                      : 'a different product')
                                  . ' and cannot enable AI Connect Pro for XenForo.'
                                  . '<br>' . self::accountLink('Find the correct key in your account'),
            'invalid_key'     => '<span style="color:#c62828;font-weight:bold">❌ Key not recognised</span>'
                                  . ' — verify the key you pasted matches the confirmation email exactly.',
            'suspended'       => '<span style="color:#c62828;font-weight:bold">❌ Suspended</span>'
                                  . ' — contact <a href="mailto:' . self::SUPPORT_EMAIL . '">'
                                  . self::SUPPORT_EMAIL . '</a>',
            'error_cached'    => '<span style="color:#f57c00;font-weight:bold">⚠️ Server unreachable</span>'
                                  . ' — using cached verdict, will retry automatically',
            'no_license'      => '<span class="badge badge--secondary">Key saved but not yet verified — click "Check license now"</span>',
            default           => '<span class="badge badge--secondary">Unknown status — click "Check license now"</span>',
        };
    }

    private static function renderPortalButton(array $status): string
    {
        // Paddle returns a per-customer subscription-management URL on valid
        // licenses. When it is not present, hide the button rather than link
        // to a dead URL.
        $url = (string) ($status['paddle_customer_portal_url'] ?? '');
        if ($url === '' || !preg_match('#^https://#', $url)) {
            return '';
        }
        // Same .formRow-explain underline override as renderCheckButton().
        return '<a href="' . htmlspecialchars($url) . '" target="_blank" rel="noopener"'
             . ' class="button button--link" style="margin-right:6px;text-decoration:none">'
             . '<span class="button-text">💳 Manage subscription (Paddle)</span></a>';
    }

    /**
     * Formats a licence-server date for display.
     *
     * The API returns ISO-8601 with milliseconds and a Z suffix
     * (e.g. 2027-07-28T21:21:38.878Z). Echoing that verbatim leaked a raw
     * machine timestamp into the Admin CP. Render it through XF's own language
     * formatter instead, so it honours the board's date format and timezone
     * exactly like every other date in the control panel.
     *
     * Falls back to the raw (escaped) value if the string cannot be parsed, so
     * an unexpected server format degrades to "shown as-is" rather than blank.
     */
    private static function formatLicenseDate($value, string $fallback = 'unknown'): string
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return htmlspecialchars($fallback);
        }

        $ts = strtotime($raw);
        if ($ts === false) {
            return htmlspecialchars($raw);
        }

        return htmlspecialchars(\XF::language()->date($ts));
    }

    private static function renderCheckButton(): string
    {
        // XF's standard confirm-overlay convention: the link is fetched with GET,
        // the controller returns a form, and THAT form does the POST. Doing a
        // direct POST from this link would fail with "This action is available
        // via POST only" because data-xf-click="overlay" issues a GET.
        $url = \XF::app()->router('admin')->buildLink('aiconnect-pro/license/check');
        // Two details are load-bearing here:
        //
        // 1. <span class="button-text"> — the markup XF's own <xf:button> emits
        //    (Templater::button()); core_button.less styles icons/spacing via
        //    .button > .button-text.
        // 2. The inline text-decoration — this button lives inside the option's
        //    .formRow-explain block, and core_formrow.less applies
        //    .m-textColoredLinks() there, which emits `.formRow-explain a {
        //    text-decoration: underline }`. That selector and `a.button` have
        //    IDENTICAL specificity (0,1,1), so the explain rule wins on source
        //    order and the button rendered underlined like a plain link. An
        //    inline style is the reliable override without shipping extra CSS.
        return '<a href="' . htmlspecialchars($url) . '" class="button button--primary"'
             . ' style="text-decoration:none"'
             . ' data-xf-click="overlay"><span class="button-text">🔄 Check license now</span></a>';
    }

    /**
     * Standard XF option validator. Trims whitespace and rejects obviously
     * malformed keys so a copy-paste with a stray space still works.
     */
    public static function verifyOption(&$value, Option $option): bool
    {
        $value = trim((string) $value);
        // Accept empty (clears the license) OR a valid-looking XFP key.
        if ($value === '' || preg_match('/^XFP-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i', $value)) {
            return true;
        }
        $option->error(
            'License key format is invalid. Expected: XFP-XXXX-XXXX-XXXX-XXXX (letters + digits, dashes required).',
            $option->option_id
        );
        return false;
    }
}
