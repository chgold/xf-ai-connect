<?php

namespace chgold\AIConnect\Repository;

use XF\Mvc\Entity\Repository;

/**
 * Repository for the Token Registry audit trail.
 *
 * All write operations are best-effort and swallow exceptions (audit logging
 * must never break user requests). Tokens are tracked by their 16-char prefix
 * — the full token never enters the registry.
 */
class TokenRegistry extends Repository
{
    /**
     * Compute the 16-char prefix used to identify a token in the registry.
     */
    public function prefixOf(string $token): string
    {
        return substr($token, 0, 16);
    }

    /**
     * Record a newly issued (or refreshed) token.
     *
     * @param string      $accessToken Full access token (only the prefix is stored)
     * @param int         $userId
     * @param string      $clientId
     * @param array       $scopes      Array of scope strings, joined with spaces
     * @param int         $expiresAt   Unix timestamp
     * @param string      $source      'generator' | 'oauth' | 'refresh'
     * @param string|null $ipAddress
     */
    public function record(
        string $accessToken,
        int $userId,
        string $clientId,
        array $scopes,
        int $expiresAt,
        string $source = 'oauth',
        ?string $ipAddress = null
    ): void {
        $prefix = $this->prefixOf($accessToken);
        if ($prefix === '') {
            return;
        }
        try {
            $this->db()->insert('xf_chgold_aiconnect_token_registry', [
                'token_prefix' => $prefix,
                'user_id'      => $userId,
                'client_id'    => $clientId,
                'scope'        => implode(' ', $scopes),
                'issued_at'    => \XF::$time,
                'expires_at'   => $expiresAt,
                'source'       => $source,
                'ip_address'   => $ipAddress,
            ]);
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'TokenRegistry::record failed: ');
        }
    }

    /**
     * Update last_used_at for a token (called on every successful bearer auth).
     */
    public function markUsed(string $accessToken): void
    {
        $prefix = $this->prefixOf($accessToken);
        if ($prefix === '') {
            return;
        }
        try {
            $this->db()->update(
                'xf_chgold_aiconnect_token_registry',
                ['last_used_at' => \XF::$time],
                'token_prefix = ? AND revoked_at IS NULL',
                $prefix
            );
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'TokenRegistry::markUsed failed: ');
        }
    }

    /**
     * Soft-delete a token by its full string.
     *
     * @return int Number of registry rows affected
     */
    public function revokeByToken(string $accessToken, ?int $revokedBy = null): int
    {
        $prefix = $this->prefixOf($accessToken);
        if ($prefix === '') {
            return 0;
        }
        return $this->revokeByPrefix($prefix, $revokedBy);
    }

    /**
     * Soft-delete a token by its 16-char prefix (admin use).
     */
    public function revokeByPrefix(string $prefix, ?int $revokedBy = null): int
    {
        if ($prefix === '') {
            return 0;
        }
        try {
            return $this->db()->update(
                'xf_chgold_aiconnect_token_registry',
                [
                    'revoked_at' => \XF::$time,
                    'revoked_by' => $revokedBy,
                ],
                'token_prefix = ? AND revoked_at IS NULL',
                $prefix
            );
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'TokenRegistry::revokeByPrefix failed: ');
            return 0;
        }
    }

    /**
     * Check whether a token has been revoked in the registry.
     */
    public function isRevoked(string $accessToken): bool
    {
        $prefix = $this->prefixOf($accessToken);
        if ($prefix === '') {
            return false;
        }
        try {
            $row = $this->db()->fetchOne(
                'SELECT id FROM xf_chgold_aiconnect_token_registry
                 WHERE token_prefix = ? AND revoked_at IS NOT NULL LIMIT 1',
                $prefix
            );
            return $row !== false && $row !== null;
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'TokenRegistry::isRevoked failed: ');
            return false;
        }
    }

    /**
     * Get a finder for the admin listing.
     */
    public function findTokensForList(): \XF\Mvc\Entity\Finder
    {
        return $this->finder('chgold\AIConnect:TokenRegistry')
            ->with(['User', 'RevokedBy'])
            ->order('issued_at', 'DESC');
    }
}
