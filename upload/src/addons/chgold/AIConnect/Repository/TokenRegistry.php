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
     * Update last_used_at, last_used_ip and last_used_ua for a token
     * (called on every successful bearer auth).
     */
    public function markUsed(string $accessToken, ?string $ip = null, ?string $ua = null): void
    {
        $prefix = $this->prefixOf($accessToken);
        if ($prefix === '') {
            return;
        }
        try {
            $this->db()->update(
                'xf_chgold_aiconnect_token_registry',
                [
                    'last_used_at' => \XF::$time,
                    'last_used_ip' => $ip,
                    'last_used_ua' => $ua,
                ],
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
     * Soft-delete a token by its row ID (for user and admin use).
     */
    public function revokeById(int $id, ?int $revokedBy = null): bool
    {
        if (!$id) {
            return false;
        }
        try {
            $affected = $this->db()->update(
                'xf_chgold_aiconnect_token_registry',
                [
                    'revoked_at' => \XF::$time,
                    'revoked_by' => $revokedBy,
                ],
                'id = ? AND revoked_at IS NULL',
                $id
            );
            return $affected > 0;
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'TokenRegistry::revokeById failed: ');
            return false;
        }
    }

    /**
     * Revoke ALL active tokens for a specific user (user's "revoke all" action).
     *
     * @return int Number of tokens revoked
     */
    public function revokeAllForUser(int $userId, ?int $revokedBy = null): int
    {
        if (!$userId) {
            return 0;
        }
        try {
            return (int)$this->db()->update(
                'xf_chgold_aiconnect_token_registry',
                [
                    'revoked_at' => \XF::$time,
                    'revoked_by' => $revokedBy,
                ],
                'user_id = ? AND revoked_at IS NULL',
                $userId
            );
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'TokenRegistry::revokeAllForUser failed: ');
            return 0;
        }
    }

    /**
     * Admin bulk: revoke all tokens that have never been used and are older than $days days.
     *
     * @return int Number of tokens revoked
     */
    public function revokeUnused(int $days = 30, ?int $revokedBy = null): int
    {
        $cutoff = \XF::$time - ($days * 86400);
        try {
            $db = $this->db();
            $db->query(
                'UPDATE xf_chgold_aiconnect_token_registry
                 SET revoked_at = ?, revoked_by = ?
                 WHERE last_used_at IS NULL AND issued_at < ? AND revoked_at IS NULL',
                [\XF::$time, $revokedBy ?? 0, $cutoff]
            );
            return $db->affectedRows();
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'TokenRegistry::revokeUnused failed: ');
            return 0;
        }
    }

    /**
     * Admin bulk: revoke all tokens not used in $days days.
     *
     * @return int Number of tokens revoked
     */
    public function revokeInactive(int $days = 180, ?int $revokedBy = null): int
    {
        $cutoff = \XF::$time - ($days * 86400);
        try {
            $db = $this->db();
            $db->query(
                'UPDATE xf_chgold_aiconnect_token_registry
                 SET revoked_at = ?, revoked_by = ?
                 WHERE last_used_at IS NOT NULL AND last_used_at < ? AND revoked_at IS NULL',
                [\XF::$time, $revokedBy ?? 0, $cutoff]
            );
            return $db->affectedRows();
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'TokenRegistry::revokeInactive failed: ');
            return 0;
        }
    }

    /**
     * Check whether the lazy cleanup should run (>24h since last run).
     */
    public function shouldRunCleanup(): bool
    {
        try {
            $last = \XF::db()->fetchOne(
                "SELECT setting_value FROM xf_ai_connect_settings WHERE setting_key = 'last_cleanup_at'"
            );
            return !$last || (time() - (int)$last) > 86400;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Run all 4 cleanup rules and update the last-run timestamp.
     *
     * Rules:
     *   1. Never used + older than 30 days  → revoke
     *   2. Last used > 180 days ago          → revoke
     *   3. Expired > 90 days ago (not revoked yet) → revoke
     *   4. Revoked > 365 days ago            → hard DELETE
     *
     * @return array{unused: int, inactive: int, expired: int, deleted: int}
     */
    public function runCleanup(): array
    {
        $db      = $this->db();
        $now     = \XF::$time;
        $results = ['unused' => 0, 'inactive' => 0, 'expired' => 0, 'deleted' => 0];

        try {
            // Rule 1: never used + older than 30 days → revoke
            $db->query(
                'UPDATE xf_chgold_aiconnect_token_registry
                 SET revoked_at = ?, revoked_by = 0
                 WHERE last_used_at IS NULL AND issued_at < ? AND revoked_at IS NULL',
                [$now, $now - 30 * 86400]
            );
            $results['unused'] = $db->affectedRows();

            // Rule 2: last used > 180 days ago → revoke
            $db->query(
                'UPDATE xf_chgold_aiconnect_token_registry
                 SET revoked_at = ?, revoked_by = 0
                 WHERE last_used_at IS NOT NULL AND last_used_at < ? AND revoked_at IS NULL',
                [$now, $now - 180 * 86400]
            );
            $results['inactive'] = $db->affectedRows();

            // Rule 3: expired > 90 days ago (and not yet revoked) → revoke
            $db->query(
                'UPDATE xf_chgold_aiconnect_token_registry
                 SET revoked_at = ?, revoked_by = 0
                 WHERE revoked_at IS NULL AND expires_at < ?',
                [$now, $now - 90 * 86400]
            );
            $results['expired'] = $db->affectedRows();

            // Rule 4: revoked > 365 days ago → hard DELETE
            $db->query(
                'DELETE FROM xf_chgold_aiconnect_token_registry
                 WHERE revoked_at IS NOT NULL AND revoked_at < ?',
                [$now - 365 * 86400]
            );
            $results['deleted'] = $db->affectedRows();

            // Persist the timestamp of this run
            $db->query(
                "INSERT INTO xf_ai_connect_settings (setting_key, setting_value)
                 VALUES ('last_cleanup_at', ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
                [$now]
            );
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'TokenRegistry::runCleanup failed: ');
        }

        return $results;
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
     * Get a finder for the admin listing (all tokens).
     */
    public function findTokensForList(): \XF\Mvc\Entity\Finder
    {
        return $this->finder('chgold\AIConnect:TokenRegistry')
            ->with(['User', 'RevokedBy'])
            ->order('issued_at', 'DESC');
    }

    /**
     * Get a finder scoped to a single user's tokens (pub listing).
     */
    public function findTokensForUser(int $userId): \XF\Mvc\Entity\Finder
    {
        return $this->finder('chgold\AIConnect:TokenRegistry')
            ->where('user_id', $userId)
            ->order('issued_at', 'DESC');
    }
}
