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
    // phpcs:ignore Generic.Files.LineLength -- long signature, cannot shorten further
    public function createAuthorizationCode($clientId, $userId, $redirectUri, $codeChallenge, $codeChallengeMethod, array $scopes, $state = null)
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
            'state' => $state,
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

        // Create access token — parseScopes defensive against legacy space-format storage
        $token = $this->createAccessToken(
            $authCode['client_id'],
            $authCode['user_id'],
            self::parseScopes($authCode['scopes'])
        );

        return $token;
    }

    /**
     * Create access token with refresh token.
     *
     * @param string $clientId
     * @param int    $userId
     * @param array  $scopes
     * @param string $source 'oauth' (default), 'refresh' or 'generator'
     */
    public function createAccessToken($clientId, $userId, array $scopes, $source = 'oauth')
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

        $this->recordInRegistry($accessToken, $userId, $clientId, $scopes, $expiresDate, $source, $refreshTokenExpiresDate);

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
     * Best-effort registry write. Failures must not break token issuance.
     */
    protected function recordInRegistry(
        string $accessToken,
        int $userId,
        string $clientId,
        array $scopes,
        int $expiresDate,
        string $source,
        ?int $refreshExpiresDate = null
    ): void {
        try {
            $request = \XF::app()->request();
            $ip = $request ? $request->getIp(true) : null;

            /** @var \chgold\AIConnect\Repository\TokenRegistry $registry */
            $registry = \XF::repository('chgold\AIConnect:TokenRegistry');
            $registry->record(
                $accessToken,
                $userId,
                $clientId,
                $scopes,
                $expiresDate,
                $source,
                $ip,
                $refreshExpiresDate
            );
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'OAuthServer::recordInRegistry failed: ');
        }
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

        try {
            /** @var \chgold\AIConnect\Repository\TokenRegistry $registry */
            $registry = \XF::repository('chgold\AIConnect:TokenRegistry');
            if ($registry->isRevoked($token)) {
                return ['valid' => false, 'error' => 'Token has been revoked'];
            }
            $ip = substr(\XF::app()->request()->getIp(), 0, 45);
            $ua = substr(\XF::app()->request()->getServer('HTTP_USER_AGENT') ?? '', 0, 255);
            $registry->markUsed($token, $ip ?: null, $ua ?: null);

            try {
                if ($registry->shouldRunCleanup()) {
                    $registry->runCleanup();
                }
            } catch (\Throwable $cleanupEx) {
                \XF::logException($cleanupEx, false, 'TokenRegistry cleanup failed: ');
            }
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'OAuthServer::validateToken registry check failed: ');
        }

        return [
            'valid' => true,
            'user_id' => $tokenData['user_id'],
            'client_id' => $tokenData['client_id'],
            'scopes' => self::parseScopes($tokenData['scopes']),
        ];
    }

    /**
     * v1.2.47 — defensive scope parser.
     *
     * Legacy tokens (created before 2026-08) stored `scopes` as a space-separated
     * string ("read write admin"). Newer tokens use JSON (["read","write","admin"]).
     * A blind json_decode on the legacy shape returns NULL, which downstream
     * ScopeGuard treats as "no scopes at all" — a valid admin token then fails
     * checkScope('admin') with "admin scope required".
     *
     * This parser accepts BOTH shapes without a schema change to the DB:
     *   - JSON array   → array_values($decoded)
     *   - space/comma  → preg_split with whitespace-or-comma delimiter
     *   - null / bad   → empty array (safe default; caller sees "no scopes")
     */
    public static function parseScopes($raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }
        if (is_array($raw)) {
            return array_values(array_filter($raw, 'is_string'));
        }
        $s = (string) $raw;
        $trim = ltrim($s);
        if ($trim !== '' && $trim[0] === '[') {
            $decoded = json_decode($s, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded, 'is_string'));
            }
        }
        // Legacy space/comma-separated format
        $parts = preg_split('/[\s,]+/', trim($s));
        if ($parts === false) {
            return [];
        }
        return array_values(array_filter($parts, static fn($p) => $p !== ''));
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

        // client_id is optional on refresh — the refresh token binds the client.
        // Reject only when a client_id IS supplied and does not match; when omitted,
        // the stored client_id is used for the new token (see createAccessToken below).
        if ($clientId !== '' && $tokenData['client_id'] !== $clientId) {
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

        // Create new access token and refresh token — parseScopes defensive against legacy format
        $newToken = $this->createAccessToken(
            $tokenData['client_id'],
            $tokenData['user_id'],
            self::parseScopes($tokenData['scopes']),
            'refresh'
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

        try {
            $visitorId = \XF::visitor()->user_id ?: null;
            /** @var \chgold\AIConnect\Repository\TokenRegistry $registry */
            $registry = \XF::repository('chgold\AIConnect:TokenRegistry');
            $registry->revokeByToken($token, $visitorId);
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'OAuthServer::revokeToken registry sync failed: ');
        }

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

        // Exact match first
        $client = $db->fetchRow(
            'SELECT * FROM xf_ai_connect_oauth_clients WHERE client_id = ?',
            $clientId
        );
        if ($client !== false) {
            return true;
        }

        // Fuzzy match: some AI agents send variations like "gemini_client", "claude_client"
        // Strip common suffixes and try the base name
        $normalized = preg_replace('/[-_](client|ai|app|bot|agent)$/i', '', $clientId);
        if ($normalized !== $clientId && !empty($normalized)) {
            $client = $db->fetchRow(
                'SELECT client_id FROM xf_ai_connect_oauth_clients WHERE client_id = ?',
                $normalized
            );
            if ($client !== false) {
                // Auto-register the variant so future lookups work directly
                $base = $db->fetchRow(
                    'SELECT * FROM xf_ai_connect_oauth_clients WHERE client_id = ?',
                    $normalized
                );
                if ($base) {
                    $db->insert('xf_ai_connect_oauth_clients', [
                        'client_id'     => $clientId,
                        'client_name'   => $base['client_name'],
                        'client_type'   => $base['client_type'],
                        'redirect_uris' => $base['redirect_uris'],
                        'allowed_scopes' => $base['allowed_scopes'],
                        'created_date'  => \XF::$time,
                        'updated_date'  => \XF::$time,
                    ], false, 'client_name = VALUES(client_name)');
                }
                return true;
            }
        }

        return false;
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

        $allowedScopes = self::parseScopes($client['allowed_scopes']);

        foreach ($requestedScopes as $scope) {
            if (!in_array($scope, $allowedScopes, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * v1.2.48 — RFC 6749 §3.3 compliant scope subsetting.
     *
     * Legacy validateScopes() was strict all-or-nothing: any unknown scope in
     * the request → whole request rejected with "Invalid scope". This broke
     * every agent whose OAuth client sent a default scope list containing a
     * scope this addon doesn't support (e.g. goldnat's "delete" — never
     * implemented here). Agents then never received an admin token and every
     * admin_* call failed with "admin scope required".
     *
     * Per RFC 6749 §3.3 the authorization server MAY grant a narrower scope
     * than requested — it just has to inform the client. This method returns
     * the intersection of (requested ∩ allowed_for_client). Caller decides:
     *   - empty result → invalid_scope (nothing to grant)
     *   - non-empty → grant that subset, echo it back in the response so the
     *     client sees what was actually granted
     */
    public function filterAllowedScopes($clientId, array $requestedScopes): array
    {
        $db = \XF::db();
        $client = $db->fetchRow(
            'SELECT allowed_scopes FROM xf_ai_connect_oauth_clients WHERE client_id = ?',
            $clientId
        );
        if (!$client) {
            return [];
        }
        $allowedScopes = self::parseScopes($client['allowed_scopes']);
        return array_values(array_intersect($requestedScopes, $allowedScopes));
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
