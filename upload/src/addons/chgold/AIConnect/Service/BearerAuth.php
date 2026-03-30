<?php

namespace chgold\AIConnect\Service;

use XF\Service\AbstractService;

class BearerAuth extends AbstractService
{
    protected $oauthServer;

    public function __construct(\XF\App $app)
    {
        parent::__construct($app);
        $this->oauthServer = \XF::service('chgold\AIConnect:OAuthServer');
    }

    public function authenticateFromBearerToken(\XF\Api\Mvc\Renderer\AbstractRenderer $renderer, \XF\Mvc\Reply\AbstractReply $reply)
    {
        if ($this->isPublicEndpoint()) {
            return;
        }

        $token = $this->getBearerToken();

        if (!$token) {
            return;
        }

        $tokenData = $this->oauthServer->validateToken($token);

        if (!$tokenData['valid']) {
            return;
        }

        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            $user = \XF::em()->find('XF:User', $tokenData['user_id']);
            if ($user) {
                \XF::setVisitor($user);
            }
        }
    }

    public function checkBearerAuth()
    {
        if ($this->isPublicEndpoint()) {
            return true;
        }

        $token = $this->getBearerToken();

        if (!$token) {
            return null;
        }

        $tokenData = $this->oauthServer->validateToken($token);

        if (!$tokenData['valid']) {
            throw new \XF\Mvc\Reply\Exception(
                \XF::apiError($tokenData['error'] ?? 'Invalid token', 'invalid_token')
            );
        }

        return $tokenData;
    }

    protected function getBearerToken()
    {
        $authHeader = $this->getAuthorizationHeader();

        if ($authHeader) {
            if (strpos($authHeader, 'Bearer ') === 0) {
                return substr($authHeader, 7);
            }
            return null;
        }

        $queryToken = \XF::app()->request()->filter('token', 'str');
        if ($queryToken) {
            return $queryToken;
        }

        return null;
    }

    protected function getAuthorizationHeader()
    {
        $request = \XF::app()->request();

        $authHeader = $request->getServer('HTTP_AUTHORIZATION');
        if ($authHeader) {
            return $authHeader;
        }

        $authHeader = $request->getServer('REDIRECT_HTTP_AUTHORIZATION');
        if ($authHeader) {
            return $authHeader;
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
        }

        return null;
    }

    protected function isPublicEndpoint()
    {
        $request = \XF::app()->request();
        $requestUri = $request->getRequestUri();

        $publicEndpoints = [
            '/api/aiconnect-oauth/token',
            '/api/aiconnect-oauth/revoke',
            '/api/aiconnect-manifest',
        ];

        foreach ($publicEndpoints as $endpoint) {
            if (strpos($requestUri, $endpoint) !== false) {
                return true;
            }
        }

        return false;
    }

    public function checkScope($requiredScope)
    {
        $tokenData = $this->checkBearerAuth();

        if (!$tokenData || !isset($tokenData['scopes'])) {
            return false;
        }

        return in_array($requiredScope, $tokenData['scopes'], true);
    }
}
