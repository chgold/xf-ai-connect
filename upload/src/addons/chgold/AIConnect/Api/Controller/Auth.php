<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Auth extends AbstractController
{
    public function actionPostLogin()
    {
        $username = $this->filter('username', 'str');
        $password = $this->filter('password', 'str');

        if (empty($username) || empty($password)) {
            return $this->error('Username and password are required', 400);
        }

        $authService = \XF::service('chgold\AIConnect:Auth');
        $result = $authService->authenticateUser($username, $password);

        if (!$result['success']) {
            return $this->error($result['error'] ?? 'Authentication failed', 401);
        }

        return $this->apiSuccess($result);
    }

    public function actionPostRefresh()
    {
        return $this->error('Refresh not implemented - tokens are long-lived', 501);
    }
}
