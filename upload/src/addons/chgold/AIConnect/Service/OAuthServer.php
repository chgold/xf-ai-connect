<?php

namespace chgold\AIConnect\Service;

use XF\Service\AbstractService;

class OAuthServer extends AbstractService
{
    protected $defaultTokenLifetime = 3600; // 1 hour
    protected $defaultCodeLifetime = 600; // 10 minutes
    protected $defaultRefreshTokenLifetime = 2592000; // 30 days

    /**
     * Generate authorization code
     */
    public function createAuthorizationCode($clientId, $userId, $redirectUri, $codeChallenge, $codeChallengeMethod, array $scopes)
    {
        $db = \XF::db();
        $time = \XF::$time;

        $code = $this->generateToken(128);
        $expiresDate = $time + $this->defaultCodeLifetime;

        $db->insert('xf_ai_connect_oauth_codes', [
            'code' => $code,
            'client_id' => $clientId,
            'user_id' => $userId,
            'redirect_uri' => $redirectUri,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => $codeChallengeMethod,
            'scopes' => json_encode($scopes),
            'expires_date' => $expiresDate,
            'created_date' => $time
        ]);

        return $code;
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken($code, $clientId, $codeVerifier, $redirectUri)
    {
        $db = \XF::db();
        $time = \XF::$time;

        $authCode = $db->fetchRow(
            'SELECT * FROM xf_ai_connect_oauth_codes WHERE code = ?',
            $code
        );

        if (!$authCode) {
            return ['error' => 'invalid_grant', 'error_description' => 'Authorization code not found'];
        }

        if ($authCode['used_date'] > 0) {
            return ['error' => 'invalid_grant', 'error_description' => 'Authorization code already used'];
        }

        if ($authCode['expires_date'] < $time) {
            return ['error' => 'invalid_grant', 'error_description' => 'Authorization code expired'];
        }

        if ($authCode['client_id'] !== $clientId) {
            return ['error' => 'invalid_client', 'error_description' => 'Client ID mismatch'];
        }

        if ($authCode['redirect_uri'] !== $redirectUri) {
            return ['error' => 'invalid_grant', 'error_description' => 'Redirect URI mismatch'];
        }

        if (!$this->verifyPKCE($codeVerifier, $authCode['code_challenge'], $authCode['code_challenge_method'])) {
            return ['error' => 'invalid_grant', 'error_description' => 'PKCE verification failed'];
        }

        // Mark code as used
        $db->update(
            'xf_ai_connect_oauth_codes',
            ['used_date' => $time],
            'code = ?',
            $code
        );

        // Create access token
        $token = $this->createAccessToken(
            $authCode['client_id'],
            $authCode['user_id'],
            json_decode($authCode['scopes'], true)
        );

        return $token;
    }

    /**
     * Create access token with refresh token
     */
    public function createAccessToken($clientId, $userId, array $scopes)
    {
        $db = \XF::db();
        $time = \XF::$time;

        $accessToken = 'xfa_' . $this->generateToken(64);
        $refreshToken = 'xfr_' . $this->generateToken(64);
        $expiresDate = $time + $this->defaultTokenLifetime;
        $refreshTokenExpiresDate = $time + $this->defaultRefreshTokenLifetime;

        $db->insert('xf_ai_connect_oauth_tokens', [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'user_id' => $userId,
            'scopes' => json_encode($scopes),
            'expires_date' => $expiresDate,
            'refresh_token_expires_date' => $refreshTokenExpiresDate,
            'created_date' => $time
        ]);

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->defaultTokenLifetime,
            'refresh_token' => $refreshToken,
            'refresh_token_expires_in' => $this->defaultRefreshTokenLifetime,
            'scope' => implode(' ', $scopes)
        ];
    }

    /**
     * Validate access token
     */
    public function validateToken($token)
    {
        $db = \XF::db();
        $time = \XF::$time;

        $tokenData = $db->fetchRow(
            'SELECT * FROM xf_ai_connect_oauth_tokens WHERE access_token = ?',
            $token
        );

        if (!$tokenData) {
            return ['valid' => false, 'error' => 'Token not found'];
        }

        if ($tokenData['revoked_date'] > 0) {
            return ['valid' => false, 'error' => 'Token has been revoked'];
        }

        if ($tokenData['expires_date'] < $time) {
            return ['valid' => false, 'error' => 'Token expired'];
        }

        return [
            'valid' => true,
            'user_id' => $tokenData['user_id'],
            'client_id' => $tokenData['client_id'],
            'scopes' => json_decode($tokenData['scopes'], true)
        ];
    }

    /**
     * Exchange refresh token for new access token
     */
    public function exchangeRefreshToken($refreshToken, $clientId)
    {
        $db = \XF::db();
        $time = \XF::$time;

        $tokenData = $db->fetchRow(
            'SELECT * FROM xf_ai_connect_oauth_tokens WHERE refresh_token = ?',
            $refreshToken
        );

        if (!$tokenData) {
            return ['error' => 'invalid_grant', 'error_description' => 'Refresh token not found'];
        }

        if ($tokenData['client_id'] !== $clientId) {
            return ['error' => 'invalid_client', 'error_description' => 'Client ID mismatch'];
        }

        if ($tokenData['revoked_date'] > 0) {
            return ['error' => 'invalid_grant', 'error_description' => 'Refresh token has been revoked'];
        }

        if ($tokenData['refresh_token_expires_date'] < $time) {
            return ['error' => 'invalid_grant', 'error_description' => 'Refresh token expired'];
        }

        // Revoke the old token
        $db->update(
            'xf_ai_connect_oauth_tokens',
            ['revoked_date' => $time],
            'token_id = ?',
            $tokenData['token_id']
        );

        // Create new access token and refresh token
        $newToken = $this->createAccessToken(
            $tokenData['client_id'],
            $tokenData['user_id'],
            json_decode($tokenData['scopes'], true)
        );

        return $newToken;
    }

    /**
     * Revoke access token
     */
    public function revokeToken($token)
    {
        $db = \XF::db();
        $time = \XF::$time;

        $updated = $db->update(
            'xf_ai_connect_oauth_tokens',
            ['revoked_date' => $time],
            'access_token = ?',
            $token
        );

        return $updated > 0;
    }

    /**
     * Verify PKCE code challenge
     */
    protected function verifyPKCE($codeVerifier, $codeChallenge, $method)
    {
        if ($method !== 'S256') {
            return false;
        }

        $computedChallenge = $this->base64urlEncode(
            hash('sha256', $codeVerifier, true)
        );

        return hash_equals($codeChallenge, $computedChallenge);
    }

    /**
     * Validate client
     */
    public function validateClient($clientId)
    {
        $db = \XF::db();

        $client = $db->fetchRow(
            'SELECT * FROM xf_ai_connect_oauth_clients WHERE client_id = ?',
            $clientId
        );

        return $client !== false;
    }

    /**
     * Validate redirect URI
     */
    public function validateRedirectUri($clientId, $redirectUri)
    {
        $db = \XF::db();

        $client = $db->fetchRow(
            'SELECT redirect_uris FROM xf_ai_connect_oauth_clients WHERE client_id = ?',
            $clientId
        );

        if (!$client) {
            return false;
        }

        $allowedUris = json_decode($client['redirect_uris'], true);
        if (!is_array($allowedUris)) {
            // Fallback: treat as single plain-text URI
            return $client['redirect_uris'] === $redirectUri;
        }
        return in_array($redirectUri, $allowedUris, true);
    }

    /**
     * Validate scopes
     */
    public function validateScopes($clientId, array $requestedScopes)
    {
        $db = \XF::db();

        $client = $db->fetchRow(
            'SELECT allowed_scopes FROM xf_ai_connect_oauth_clients WHERE client_id = ?',
            $clientId
        );

        if (!$client) {
            return false;
        }

        $allowedScopes = json_decode($client['allowed_scopes'], true);
        if (!is_array($allowedScopes)) {
            // Fallback: treat as comma-separated string
            $allowedScopes = array_map('trim', explode(',', $client['allowed_scopes']));
        }

        foreach ($requestedScopes as $scope) {
            if (!in_array($scope, $allowedScopes, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get client info
     */
    public function getClient($clientId)
    {
        $db = \XF::db();

        return $db->fetchRow(
            'SELECT * FROM xf_ai_connect_oauth_clients WHERE client_id = ?',
            $clientId
        );
    }

    /**
     * Generate random token
     */
    protected function generateToken($length = 64)
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Base64 URL encoding (RFC 7636)
     */
    protected function base64urlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
