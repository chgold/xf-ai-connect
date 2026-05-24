<?php

namespace chgold\AIConnect\Helper;

/**
 * Navigation helpers for the AI Connect add-on.
 *
 * Provides cheap, request-cached checks used by navigation entry
 * display_condition expressions so we don't run expensive queries
 * on every page load.
 */
class Nav
{
    /**
     * Returns true when the given user has at least one manageable token —
     * one that is NOT revoked AND whose access token is still alive OR
     * whose refresh token is still alive (renewable).
     *
     * Result is cached per-request to keep the check cheap when used
     * inside navigation display conditions.
     */
    public static function userHasActiveTokens($userId): bool
    {
        $userId = (int)$userId;
        if (!$userId) {
            return false;
        }

        static $cache = [];
        if (isset($cache[$userId])) {
            return $cache[$userId];
        }

        try {
            $now = \XF::$time;
            $count = (int)\XF::db()->fetchOne(
                "SELECT COUNT(*) FROM xf_chgold_aiconnect_token_registry
                 WHERE user_id = ?
                   AND (revoked_at IS NULL OR revoked_at = 0)
                   AND (expires_at > ? OR refresh_expires_at > ?)",
                [$userId, $now, $now]
            );
            return $cache[$userId] = ($count > 0);
        } catch (\Throwable $e) {
            return $cache[$userId] = false;
        }
    }
}
