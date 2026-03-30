<?php

namespace chgold\AIConnect\Listener;

class ApiAuth
{
    public static function validateApiRequest(\XF\Http\Request $request, &$result, &$error, &$code)
    {
        $requestUri = $request->getRequestUri();

        $publicEndpoints = [
            '/api/aiconnect-manifest',
            '/api/ai-connect/manifest',
            '/api/aiconnect-oauth/token',
            '/api/aiconnect-oauth/revoke',
        ];
        foreach ($publicEndpoints as $endpoint) {
            if (strpos($requestUri, $endpoint) !== false) {
                $result = \XF::repository('XF:User')->getGuestUser();
                return;
            }
        }

        $protectedEndpoints = [
            '/api/aiconnect-tools',
            '/api/ai-connect/v1/tools',
        ];
        $isOurEndpoint = false;
        foreach ($protectedEndpoints as $endpoint) {
            if (strpos($requestUri, $endpoint) !== false) {
                $isOurEndpoint = true;
                break;
            }
        }

        $authHeader = $request->getServer('HTTP_AUTHORIZATION');
        $queryToken = $request->filter('token', 'str');

        $bearerToken = null;
        if ($authHeader && strpos($authHeader, 'Bearer ') === 0) {
            $bearerToken = substr($authHeader, 7);
        } elseif ($queryToken) {
            $bearerToken = $queryToken;
        }

        if ($isOurEndpoint && !$bearerToken) {
            $error = 'api_error.bearer_token_required';
            $code = 401;
            $result = false;
            return;
        }

        if (!$bearerToken) {
            return;
        }

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
