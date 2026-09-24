<?php

namespace chgold\AIConnect\Service;

use XF\Service\AbstractService;

class RateLimiter extends AbstractService
{
    /**
     * Check if identifier is rate limited.
     *
     * @param string      $identifier per-user key (e.g. "user_5")
     * @param string|null $category   roadmap item 5 (finer-grained): 'read' or
     *   'write'. When supplied AND the admin has set a positive category limit,
     *   an ADDITIONAL window is enforced under "{identifier}:{category}", on top
     *   of the global per-minute/per-hour caps. Omitted / category limit = 0 ->
     *   behaves exactly as before (global flat limit only). This lets an admin
     *   run generous reads + conservative writes.
     */
    public function isRateLimited($identifier, $category = null)
    {
        // Roadmap (rate-limit ACP UI): read the admin-editable native XF options
        // first; fall back to the legacy custom-settings-table values (and their
        // hard defaults) so existing installs keep their configured limits until
        // an admin touches the new ACP controls.
        $opts = \XF::options();
        $perMinute = isset($opts->aiconnect_rate_limit_per_minute) && $opts->aiconnect_rate_limit_per_minute !== ''
            ? (int) $opts->aiconnect_rate_limit_per_minute
            : (int) Settings::get('rate_limit_per_minute', 50);
        $perHour = isset($opts->aiconnect_rate_limit_per_hour) && $opts->aiconnect_rate_limit_per_hour !== ''
            ? (int) $opts->aiconnect_rate_limit_per_hour
            : (int) Settings::get('rate_limit_per_hour', 1000);

        // Global per-minute / per-hour caps (unchanged behaviour).
        $minuteCheck = $this->checkWindow($identifier, 'minute', 60, $perMinute);
        if ($minuteCheck['limited']) {
            return $minuteCheck;
        }
        $hourCheck = $this->checkWindow($identifier, 'hour', 3600, $perHour);
        if ($hourCheck['limited']) {
            return $hourCheck;
        }

        // Roadmap item 5: optional per-category (read/write) per-minute cap.
        // Only enforced when the admin set a positive limit for that category —
        // otherwise this whole block is a no-op (backward compatible).
        $catLimit = $this->categoryLimit($category);
        if ($catLimit > 0) {
            $catCheck = $this->checkWindow($identifier . ':' . $category, 'minute', 60, $catLimit);
            if ($catCheck['limited']) {
                $catCheck['reason'] = sprintf('%d %s requests per minute', $catLimit, $category);
                return $catCheck;
            }
        }

        return ['limited' => false];
    }

    /**
     * Admin-configured per-minute limit for a request category, or 0 (disabled).
     * Options: aiconnect_rate_limit_read_per_minute / _write_per_minute.
     */
    protected function categoryLimit($category): int
    {
        if ($category !== 'read' && $category !== 'write') {
            return 0;
        }
        $opt = 'aiconnect_rate_limit_' . $category . '_per_minute';
        $opts = \XF::options();
        return isset($opts->$opt) && $opts->$opt !== '' ? (int) $opts->$opt : 0;
    }

    /**
     * Record a request.
     *
     * @param string      $identifier per-user key
     * @param string|null $category   'read'/'write' — increments the matching
     *   category window too, so the category cap in isRateLimited() has data.
     */
    public function recordRequest($identifier, $category = null)
    {
        $this->incrementWindow($identifier, 'minute', 60);
        $this->incrementWindow($identifier, 'hour', 3600);

        if (($category === 'read' || $category === 'write') && $this->categoryLimit($category) > 0) {
            $this->incrementWindow($identifier . ':' . $category, 'minute', 60);
        }
    }

    /**
     * Check rate limit for a time window
     */
    protected function checkWindow($identifier, $windowType, $windowSize, $limit)
    {
        $now = time();
        $windowStart = floor($now / $windowSize) * $windowSize;

        $record = \XF::db()->fetchRow(
            'SELECT * FROM xf_ai_connect_rate_limits 
             WHERE identifier = ? AND window_type = ? AND window_start = ?',
            [$identifier, $windowType, $windowStart]
        );

        if (!$record) {
            return ['limited' => false];
        }

        if ($record['request_count'] >= $limit) {
            $retryAfter = $windowStart + $windowSize - $now;
            return [
                'limited' => true,
                'reason' => sprintf('%d requests per %s', $limit, $windowType),
                'retry_after' => $retryAfter,
                'limit' => $limit,
                'current' => $record['request_count'],
            ];
        }

        return ['limited' => false];
    }

    /**
     * Increment request counter for window
     */
    protected function incrementWindow($identifier, $windowType, $windowSize)
    {
        $now = time();
        $windowStart = floor($now / $windowSize) * $windowSize;

        \XF::db()->query(
            'INSERT INTO xf_ai_connect_rate_limits (identifier, window_type, window_start, request_count, last_request_date)
             VALUES (?, ?, ?, 1, ?)
             ON DUPLICATE KEY UPDATE 
                request_count = request_count + 1,
                last_request_date = VALUES(last_request_date)',
            [$identifier, $windowType, $windowStart, $now]
        );

        // Clean up old windows (older than 24 hours)
        \XF::db()->delete('xf_ai_connect_rate_limits', 'window_start < ?', $now - 86400);
    }
}
