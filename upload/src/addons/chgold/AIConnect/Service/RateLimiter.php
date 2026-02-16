<?php

namespace chgold\AIConnect\Service;

use XF\Service\AbstractService;

class RateLimiter extends AbstractService
{
    /**
     * Check if identifier is rate limited
     */
    public function isRateLimited($identifier)
    {
        $perMinute = (int) Settings::get('rate_limit_per_minute', 50);
        $perHour = (int) Settings::get('rate_limit_per_hour', 1000);
        
        // Check per-minute limit
        $minuteCheck = $this->checkWindow($identifier, 'minute', 60, $perMinute);
        if ($minuteCheck['limited']) {
            return $minuteCheck;
        }

        // Check per-hour limit
        $hourCheck = $this->checkWindow($identifier, 'hour', 3600, $perHour);
        if ($hourCheck['limited']) {
            return $hourCheck;
        }

        return ['limited' => false];
    }

    /**
     * Record a request
     */
    public function recordRequest($identifier)
    {
        $this->incrementWindow($identifier, 'minute', 60);
        $this->incrementWindow($identifier, 'hour', 3600);
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
