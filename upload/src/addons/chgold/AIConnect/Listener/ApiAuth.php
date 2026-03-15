<?php

namespace chgold\AIConnect\Listener;

class ApiAuth
{
    public static function validateApiRequest(\XF\Http\Request $request, &$result, &$error, &$code)
    {
        // Allow public endpoints without API key or Bearer token
        $requestUri = $request->getRequestUri();
        $publicEndpoints = [
            '/api/aiconnect-manifest',
            '/api/aiconnect-oauth/token',
            '/api/aiconnect-oauth/revoke',
        ];
        foreach ($publicEndpoints as $endpoint) {
            if (strpos($requestUri, $endpoint) !== false) {
                $result = \XF::repository('XF:User')->getGuestUser();
                return;
            }
        }

        $authHeader = $request->getServer('HTTP_AUTHORIZATION');

        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            return;
        }

        $bearerToken = substr($authHeader, 7);

        $oauthServer = \XF::service('chgold\AIConnect:OAuthServer');
        $tokenData = $oauthServer->validateToken($bearerToken);

        if (!$tokenData['valid']) {
            $error = 'api_error.invalid_bearer_token';
            $code = 401;
            $result = false;
            return;
        }

        $userRepo = \XF::repository('XF:User');
        $user = $userRepo->getVisitor($tokenData['user_id']);

        if (!$user || $user->user_id != $tokenData['user_id']) {
            $error = 'api_error.user_not_found';
            $code = 401;
            $result = false;
            return;
        }

        $result = $user;
    }
}
