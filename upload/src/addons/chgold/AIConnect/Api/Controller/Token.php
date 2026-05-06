<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Token extends AbstractController
{
    public function actionPost()
    {
        $input = $this->getInputFromRequest();
        $grantType = $input['grant_type'] ?? null;

        if ($grantType === 'authorization_code') {
            return $this->handleAuthorizationCodeGrant($input);
        }

        if ($grantType === 'refresh_token') {
            return $this->handleRefreshTokenGrant($input);
        }

        return $this->error('unsupported_grant_type', 400);
    }

    protected function getInputFromRequest()
    {
        $contentType = $this->request()->getServer('CONTENT_TYPE', '');

        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = $this->request()->getInputRaw();
            $decoded = @json_decode($rawInput, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $this->request()->filter([
            'grant_type' => 'str',
            'code' => 'str',
            'client_id' => 'str',
            'code_verifier' => 'str',
            'redirect_uri' => 'str',
            'refresh_token' => 'str',
            'token' => 'str'
        ]);
    }

    protected function handleAuthorizationCodeGrant($input)
    {
        $code = $input['code'] ?? '';
        $clientId = $input['client_id'] ?? '';
        $codeVerifier = $input['code_verifier'] ?? '';
        $redirectUri = $input['redirect_uri'] ?? '';

        if (empty($code) || empty($clientId) || empty($codeVerifier)) {
            return $this->error('invalid_request', 400);
        }

        $oauthServer = \XF::service('chgold\AIConnect:OAuthServer');
        $token = $oauthServer->exchangeCodeForToken(
            $code,
            $clientId,
            $codeVerifier,
            $redirectUri
        );

        if (isset($token['error'])) {
            return $this->error($token['error_description'] ?? $token['error'], 400);
        }

        return $this->apiSuccess($token);
    }

    protected function handleRefreshTokenGrant($input)
    {
        $refreshToken = $input['refresh_token'] ?? '';
        $clientId = $input['client_id'] ?? '';

        if (empty($refreshToken) || empty($clientId)) {
            return $this->error('invalid_request', 400);
        }

        $oauthServer = \XF::service('chgold\AIConnect:OAuthServer');
        $token = $oauthServer->exchangeRefreshToken(
            $refreshToken,
            $clientId
        );

        if (isset($token['error'])) {
            return $this->error($token['error_description'] ?? $token['error'], 400);
        }

        return $this->apiSuccess($token);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function actionGetAuthorize()
    {
        // MCP clients construct authorization URL as token_url + /authorize
        // Forward all OAuth params to the actual consent page (oauth.php)
        $req = $this->request();

        // Default client_id to 'claude' — MCP clients often omit it in the authorize request
        $params = array_filter([
            'response_type'         => $req->filter('response_type', 'str') ?: 'code',
            'client_id'             => $req->filter('client_id', 'str') ?: 'claude',
            'redirect_uri'          => $req->filter('redirect_uri', 'str'),
            'scope'                 => $req->filter('scope', 'str'),
            'state'                 => $req->filter('state', 'str'),
            'code_challenge'        => $req->filter('code_challenge', 'str'),
            'code_challenge_method' => $req->filter('code_challenge_method', 'str'),
        ]);

        $scheme = $req->getServer('HTTPS') === 'on' ? 'https' : 'http';
        $host   = $req->getServer('HTTP_HOST') ?: $req->getServer('SERVER_NAME');
        $base   = $scheme . '://' . $host;

        return $this->redirect($base . '/oauth.php?' . http_build_query($params), '', 'temporary');
    }

    public function actionPostRevoke()
    {
        $input = $this->getInputFromRequest();
        $token = $input['token'] ?? '';

        if (empty($token)) {
            return $this->error('invalid_request', 400);
        }

        $oauthServer = \XF::service('chgold\AIConnect:OAuthServer');
        $revoked = $oauthServer->revokeToken($token);

        return $this->apiSuccess([
            'revoked' => $revoked
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function actionGetStart()
    {
        $req      = $this->request();
        $clientId = $req->filter('client_id', 'str') ?: 'claude';

        $codeVerifier  = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
        $sessionId     = bin2hex(random_bytes(16));

        $db   = \XF::db();
        $time = \XF::$time;

        $db->insert('xf_ai_connect_auth_sessions', [
            'session_id'     => $sessionId,
            'client_id'      => $clientId,
            'code_verifier'  => $codeVerifier,
            'code_challenge' => $codeChallenge,
            'expires_date'   => $time + 600,
            'created_date'   => $time,
        ]);

        $boardUrl = rtrim(\XF::options()->boardUrl ?? '', '/');
        if ($boardUrl === '') {
            $forwarded = $req->getServer('HTTP_X_FORWARDED_PROTO');
            $scheme    = $forwarded ?: ($req->getServer('HTTPS') === 'on' ? 'https' : 'http');
            $host      = $req->getServer('HTTP_HOST') ?: $req->getServer('SERVER_NAME');
            $boardUrl  = rtrim($scheme . '://' . $host, '/');
        }

        $authUrl = $boardUrl . '/oauth.php?' . http_build_query([
            'client_id'             => $clientId,
            'response_type'         => 'code',
            'redirect_uri'          => 'urn:ietf:wg:oauth:2.0:oob',
            'scope'                 => 'read write',
            'state'                 => $sessionId,
            'code_challenge'        => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        $pollUrl = $boardUrl . '/api/aiconnect-oauth/poll?session_id=' . $sessionId;

        return $this->apiSuccess([
            'session_id' => $sessionId,
            'auth_url'   => $authUrl,
            'poll_url'   => $pollUrl,
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function actionGetPoll()
    {
        $req       = $this->request();
        $sessionId = $req->filter('session_id', 'str');

        if (empty($sessionId)) {
            return $this->error('invalid_request: session_id required', 400);
        }

        $db   = \XF::db();
        $time = \XF::$time;

        $session = $db->fetchRow(
            'SELECT * FROM xf_ai_connect_auth_sessions WHERE session_id = ? AND expires_date > ?',
            [$sessionId, $time]
        );

        if (!$session) {
            return $this->error('session_expired', 400);
        }

        $authCode = $db->fetchRow(
            'SELECT * FROM xf_ai_connect_oauth_codes WHERE state = ? AND used_date = 0 AND expires_date > ?',
            [$sessionId, $time]
        );

        if (!$authCode) {
            return $this->apiSuccess(['status' => 'pending']);
        }

        $oauthServer = \XF::service('chgold\AIConnect:OAuthServer');
        $token = $oauthServer->exchangeCodeForToken(
            $authCode['code'],
            $session['client_id'],
            $session['code_verifier'],
            'urn:ietf:wg:oauth:2.0:oob'
        );

        $db->delete('xf_ai_connect_auth_sessions', 'session_id = ?', $sessionId);

        if (isset($token['error'])) {
            return $this->error($token['error_description'] ?? $token['error'], 400);
        }

        return $this->apiSuccess(array_merge(['status' => 'authorized'], $token));
    }

    public function allowUnauthenticatedRequest($action)
    {
        return true;
    }

    public function assertRequiredApiInput($keys)
    {
        return [];
    }
}
