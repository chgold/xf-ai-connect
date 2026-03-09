<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Auth extends AbstractController
{
    public function actionPostLogin()
    {
        return $this->error('DEPRECATED: Password authentication is deprecated. Use OAuth 2.0 flow instead. Visit /oauth/authorize to begin OAuth flow.', 410);
    }

    public function actionPostRefresh()
    {
        return $this->error('DEPRECATED: Use OAuth 2.0 refresh_token flow via /api/aiconnect-oauth/token', 410);
    }

    public function allowUnauthenticatedRequest($action)
    {
        return true;
    }
}
