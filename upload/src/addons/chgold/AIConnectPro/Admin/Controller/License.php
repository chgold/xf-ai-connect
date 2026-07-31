<?php

namespace chgold\AIConnectPro\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class License extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        $status = \chgold\AIConnectPro\License\Validator::getStatus();
        $key    = \chgold\AIConnectPro\License\Validator::getLicenseKey();

        $msg     = $this->filter('msg', 'str');
        $msgType = ($msg && strpos($msg, 'valid') === 0) ? 'success' : 'error';

        return $this->view(
            'chgold\AIConnectPro:License\Index',
            'aiconnect_pro_license',
            ['status' => $status, 'license_key' => $key, 'msg' => $msg, 'msgType' => $msgType]
        );
    }

    public function actionCheck(ParameterBag $params)
    {
        $this->assertPostOnly();

        // Persist the key the admin entered in the license form before validating,
        // so Validator::check() reads the freshly-saved key.
        $key = $this->filter('options', 'array')['aiconnect_pro_license_key'] ?? null;
        if ($key !== null) {
            $this->repository('XF:Option')->updateOptions([
                'aiconnect_pro_license_key' => trim((string)$key),
            ]);
        }

        $status = \chgold\AIConnectPro\License\Validator::check(true);

        $label = match ($status['status'] ?? '') {
            'valid'           => 'License valid — updates active until ' . ($status['updates_expire_at'] ?? ''),
            'valid_no_updates'=> 'License valid (perpetual) — updates expired ' . ($status['updates_expire_at'] ?? '') . '. Renew for updates.',
            'invalid_domain'  => 'License is registered to a different domain: ' . ($status['licensed_domain'] ?? ''),
            'invalid_key'     => 'License key not found. Check your key.',
            'suspended'       => 'License suspended. Contact support.',
            'no_license'      => 'No license key entered.',
            default           => 'Could not reach license server — will retry next week.',
        };

        return $this->redirect(
            $this->buildLink('aiconnect-pro/license') . '?check=1&msg=' . urlencode($label)
        );
    }

    protected function preDispatchController($action, ParameterBag $params): void
    {
        $this->assertAdminPermission('option');
    }
}
