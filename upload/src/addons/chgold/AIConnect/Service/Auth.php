<?php

namespace chgold\AIConnect\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use XF\Service\AbstractService;

class Auth extends AbstractService
{
    /**
     * Generate JWT access token
     */
    public function generateAccessToken($userId, $scopes = ['read', 'write'])
    {
        $secret = Settings::get('jwt_secret');
        $expiry = (int) Settings::get('token_expiry', 3600);
        
        $payload = [
            'iss' => \XF::options()->boardUrl,
            'iat' => time(),
            'exp' => time() + $expiry,
            'user_id' => $userId,
            'scopes' => $scopes,
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * Validate JWT access token
     */
    public function validateAccessToken($token)
    {
        try {
            $secret = Settings::get('jwt_secret');
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            
            return [
                'valid' => true,
                'user_id' => $decoded->user_id,
                'scopes' => $decoded->scopes ?? ['read'],
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate API key
     */
    public function generateApiKey($userId, $name, $scopes = ['read', 'write'])
    {
        $apiKey = bin2hex(random_bytes(32));
        
        \XF::db()->insert('xf_ai_connect_api_keys', [
            'user_id' => $userId,
            'api_key' => $apiKey,
            'name' => $name,
            'scopes' => serialize($scopes),
            'is_active' => 1,
            'created_date' => time(),
        ]);

        return $apiKey;
    }

    /**
     * Validate API key
     */
    public function validateApiKey($apiKey)
    {
        $key = \XF::db()->fetchRow(
            'SELECT * FROM xf_ai_connect_api_keys WHERE api_key = ? AND is_active = 1',
            $apiKey
        );

        if (!$key) {
            return ['valid' => false];
        }

        // Update last used
        \XF::db()->update('xf_ai_connect_api_keys', [
            'last_used_date' => time()
        ], 'api_key_id = ?', $key['api_key_id']);

        return [
            'valid' => true,
            'user_id' => $key['user_id'],
            'scopes' => unserialize($key['scopes']),
        ];
    }

    /**
     * Authenticate user with username/password
     */
    public function authenticateUser($username, $password)
    {
        /** @var \XF\Service\User\Login $loginService */
        $loginService = $this->service('XF:User\Login', $username, \XF::app()->request()->getIp());
        
        $user = $loginService->validate($password, $error);
        if (!$user) {
            return [
                'success' => false,
                'error' => $error ?? 'Invalid credentials',
            ];
        }
        
        // Check if user is blocked
        $blocked = \XF::db()->fetchOne(
            'SELECT user_id FROM xf_ai_connect_blocked_users WHERE user_id = ?',
            $user->user_id
        );

        if ($blocked) {
            return [
                'success' => false,
                'error' => 'Access denied - user blocked from API access',
            ];
        }

        $accessToken = $this->generateAccessToken($user->user_id);

        return [
            'success' => true,
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => (int) Settings::get('token_expiry', 3600),
            'user_id' => $user->user_id,
            'username' => $user->username,
        ];
    }
}
