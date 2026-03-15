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

    public function allowUnauthenticatedRequest($action)
    {
        error_log('[AIConnect Token] allowUnauthenticatedRequest called for action: ' . $action);
        return true;
    }

    public function assertRequiredApiInput($keys)
    {
        return [];
    }
}
