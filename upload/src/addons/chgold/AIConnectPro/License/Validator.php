<?php

namespace chgold\AIConnectPro\License;

class Validator
{
    private const API_URL     = 'https://goldnat.ai/api/licenses/public/validate';
    private const EXPECTED_SLUG = 'xenforo-addon-pro';
    private const OPTION_KEY  = 'aiconnect_pro_license_key';
    private const STATUS_KEY  = 'aiconnect_pro_license_status';
    private const CHECKED_KEY = 'aiconnect_pro_license_checked';

    /**
     * Server verdict when the license belongs to a different plugin/platform
     * (e.g. a WordPress license presented by this XenForo add-on). Treated
     * exactly like an invalid license: no tools, clear message.
     */
    public const STATUS_PLUGIN_MISMATCH = 'plugin_mismatch';

    /**
     * Account area on goldnat.ai where the customer manages licenses.
     *
     * Domain changes are performed HERE, never from inside this add-on: a site
     * must not be able to reassign a license to itself. The page also exposes
     * the add-on download (which unlocks only once a domain is set) and the
     * remaining domain-change allowance. Requires SSO sign-in.
     */
    public const ACCOUNT_URL = 'https://goldnat.ai/account/licenses';

    public static function getLicenseKey(): string
    {
        return (string)self::readOption(self::OPTION_KEY);
    }

    public static function getStatus(): array
    {
        $cached = self::readOption(self::STATUS_KEY);
        return $cached ? json_decode($cached, true) : [];
    }

    /**
     * Read an option defensively — before the first license check (or on a fresh
     * install) the option may not be registered yet, and XF\Options throws an
     * E_WARNING on an undefined key, which would break Pro tool loading.
     *
     * EVERY read of a license option MUST go through this method. Reading
     * \XF::options()->{$key} directly throws ErrorException when the option row
     * is missing (observed on the license_checked key), which surfaced as a
     * fatal error in the ACP "Check license now" action.
     */
    private static function readOption(string $key): string
    {
        $options = \XF::options();
        return $options->offsetExists($key) ? (string)$options->{$key} : '';
    }

    /**
     * Persist an option even when it is not registered in xf_option.
     *
     * XF's Option repository silently no-ops for unknown option ids, so the
     * status cache was never actually written on installs where the
     * status/checked options had not been imported. We therefore ensure the row
     * exists before delegating to the repository, keeping the normal XF cache
     * rebuild behaviour intact.
     */
    private static function writeOptions(array $values): void
    {
        $db = \XF::db();

        foreach ($values as $key => $value) {
            $exists = (int)$db->fetchOne(
                'SELECT COUNT(*) FROM xf_option WHERE option_id = ?',
                [$key]
            );

            if (!$exists) {
                $db->insert('xf_option', [
                    'option_id'          => $key,
                    'option_value'       => (string)$value,
                    'default_value'      => '',
                    'edit_format'        => 'textbox',
                    'edit_format_params' => '',
                    'data_type'          => 'string',
                    'sub_options'        => '',
                    'validation_class'   => '',
                    'validation_method'  => '',
                    'advanced'           => 1,
                    'addon_id'           => 'chgold/AIConnectPro',
                ], false, false);
            }
        }

        \XF::app()->repository('XF:Option')->updateOptions($values);

        // Refresh the in-memory options container.
        //
        // SECURITY-RELEVANT: updateOptions() persists to the DB but does NOT
        // update the already-loaded \XF::options() container for the current
        // request. Without this, any code that calls check() and then
        // isValid()/getBundles() in the same request reads the PREVIOUS
        // verdict — so a license that has just been revoked or replaced with an
        // invalid key would keep granting Pro tools for the rest of that
        // request. Writing the fresh values back keeps the container in sync.
        $options = \XF::options();
        foreach ($values as $key => $value) {
            $options->offsetSet($key, (string)$value);
        }
    }

    public static function isValid(): bool
    {
        // No key on file → not licensed, regardless of what the cache says.
        //
        // SECURITY-RELEVANT: the cached verdict outlives the key it was derived
        // from. Clearing the key in the ACP (the documented way to unlicense a
        // site, e.g. before moving the licence elsewhere) left the previous
        // "valid" verdict in place, so every Pro tool kept loading until the
        // cache happened to be refreshed. The key is the source of truth for
        // "is this site licensed at all" — the cache only describes a key.
        if (self::getLicenseKey() === '') {
            return false;
        }

        $status = self::getStatus();
        $state  = $status['status'] ?? '';

        // Server-side platform rejection — a license issued for another plugin.
        // Checked before the fail-open branch so it can never be mistaken for a
        // network blip and silently granted.
        if ($state === self::STATUS_PLUGIN_MISMATCH) {
            return false;
        }

        // error_cached is the fail-open network-blip verdict (no slug present).
        if ($state === 'error_cached') {
            return true;
        }

        // Require matching plugin_slug so another plugin's license can't unlock
        // this one. Retained as defence-in-depth even though the server now
        // enforces the same rule via plugin_slug on the request.
        return in_array($state, ['valid', 'valid_no_updates'], true)
            && ($status['plugin_slug'] ?? '') === self::EXPECTED_SLUG;
    }

    /**
     * Bundles granted by the current license (see BUNDLES-LICENSE-SPEC.md).
     *
     * Return semantics — feed straight into `in_array($bundle, $bundles, true)`
     * plus a `'*'` sentinel check:
     *   - ["*"]              → license grants every bundle. Default fallback for
     *                          fail-open (`error_cached`) and for old cached
     *                          verdicts that predate this field so upgrading
     *                          customers are never silently downgraded.
     *   - ["moderation",...] → license grants ONLY these bundles.
     *   - []                 → no bundles (no valid license, or explicit empty).
     *
     * Called by ProModule::registerTools() to gate trait loading.
     */
    public static function getBundles(): array
    {
        // AICONNECT_EDITION=pro dev override → grant all bundles unconditionally.
        if (strtolower((string)(getenv('AICONNECT_EDITION') ?: '')) === 'pro') {
            return ['*'];
        }
        // No key on file → no bundles. Checked BEFORE the fail-open branch,
        // otherwise a stale 'error_cached' verdict would keep granting full
        // access to a site whose licence key has been removed.
        if (self::getLicenseKey() === '') {
            return [];
        }
        $status = self::getStatus();
        // Fail-open on network blip: keep the customer at full access.
        if (($status['status'] ?? '') === 'error_cached') {
            return ['*'];
        }
        // Not a valid license for THIS plugin → no bundles.
        if (!self::isValid()) {
            return [];
        }
        // Old cache without the bundles field → assume full access (backward-compat).
        if (!array_key_exists('bundles', $status)) {
            return ['*'];
        }
        $bundles = $status['bundles'];
        if (!is_array($bundles)) {
            return ['*'];
        }
        return $bundles;
    }

    /**
     * Convenience check: does the current license grant a specific bundle?
     * The '*' wildcard grants everything.
     */
    public static function hasBundle(string $bundle): bool
    {
        $b = self::getBundles();
        return in_array('*', $b, true) || in_array($bundle, $b, true);
    }

    public static function hasUpdates(): bool
    {
        return (self::getStatus()['status'] ?? '') === 'valid';
    }

    public static function check(bool $force = false): array
    {
        $key = self::getLicenseKey();

        if (!$key) {
            return self::storeStatus(['valid' => false, 'status' => 'no_license']);
        }

        $last = (int)self::readOption(self::CHECKED_KEY);
        if (!$force && $last && (time() - $last) < 86400) {
            return self::getStatus();
        }

        $domain = self::getCurrentDomain();

        // addon_version MUST be a string — the licensing API rejects an integer
        // with "Expected string, received number", which previously surfaced as
        // a generic VALIDATION_ERROR verdict on every check.
        //
        // plugin_slug moves platform enforcement to the server: the API rejects
        // a license issued for a different plugin with status=plugin_mismatch,
        // which a client-side check alone could be patched out of. Older
        // servers that do not know the field simply ignore it.
        //
        // The field is only sent when non-empty. The server treats a PRESENT
        // but empty value as a mismatch (and null as a schema error) — only a
        // fully omitted field takes the legacy path. Since EXPECTED_SLUG is a
        // compile-time constant this is belt-and-braces, but it guarantees we
        // never self-inflict a plugin_mismatch by sending a blank value.
        $payload = [
            'license_key'   => $key,
            'domain'        => $domain,
            'addon_version' => (string)\XF::$versionId,
        ];
        if (self::EXPECTED_SLUG !== '') {
            $payload['plugin_slug'] = self::EXPECTED_SLUG;
        }

        $response = self::apiCall($payload);

        if ($response === null) {
            return self::storeStatus([
                'valid' => true, 'status' => 'error_cached', 'error' => 'Could not reach license server',
            ]);
        }

        return self::storeStatus($response);
    }

    private static function getCurrentDomain(): string
    {
        $url = \XF::options()->boardUrl ?? '';
        $host = parse_url($url, PHP_URL_HOST) ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
        return strtolower($host);
    }

    private static function apiCall(array $data): ?array
    {
        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $result = curl_exec($ch);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err || !$result) {
            return null;
        }

        return json_decode($result, true);
    }

    private static function storeStatus(array $status): array
    {
        self::writeOptions([
            self::STATUS_KEY  => json_encode($status),
            self::CHECKED_KEY => (string)time(),
        ]);
        return $status;
    }
}
