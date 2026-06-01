<?php

namespace chgold\AIConnectPro\License;

class Validator
{
    private const API_URL     = 'https://gold-t.co.il/plugins-manager/api/license.php';
    private const OPTION_KEY  = 'aiconnect_pro_license_key';
    private const STATUS_KEY  = 'aiconnect_pro_license_status';
    private const CHECKED_KEY = 'aiconnect_pro_license_checked';

    public static function getLicenseKey(): string
    {
        return (string)\XF::options()->{self::OPTION_KEY};
    }

    public static function getStatus(): array
    {
        $cached = \XF::options()->{self::STATUS_KEY};
        return $cached ? json_decode($cached, true) : [];
    }

    public static function isValid(): bool
    {
        $status = self::getStatus();
        return in_array($status['status'] ?? '', ['valid', 'valid_no_updates'], true);
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

        $last = (int)\XF::options()->{self::CHECKED_KEY};
        if (!$force && $last && (time() - $last) < 604800) {
            return self::getStatus();
        }

        $domain = self::getCurrentDomain();

        $response = self::apiCall([
            'action'        => 'validate',
            'license_key'   => $key,
            'domain'        => $domain,
            'addon_version' => \XF::$versionId,
        ]);

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
        $app = \XF::app();
        $app->repository('XF:Option')->updateOptions([
            self::STATUS_KEY  => json_encode($status),
            self::CHECKED_KEY => (string)time(),
        ]);
        return $status;
    }
}
