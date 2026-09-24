<?php

namespace chgold\AIConnect\Service;

use XF\Service\AbstractService;

/**
 * Idempotency store (roadmap item 6): optional duplicate-write protection.
 *
 * When a POST (write) request supplies an Idempotency-Key (header or body), a
 * pending->completed row — guarded by a UNIQUE (user_id, idempotency_key) —
 * ensures a retried write executes at most once:
 *   - key never seen        -> reserve a 'pending' row, execute, cache success
 *   - same key + same body  -> replay the cached success (no re-execute)
 *   - same key, in-flight    -> 409 (first request still running)
 *   - same key, DIFFERENT body -> 422 (client bug: key reused with a new payload)
 *   - failure               -> release the key so a retry can re-attempt
 *
 * Backward-compatible: NO key supplied -> this whole path is skipped, behaviour
 * is exactly as before. Per-user scoped. 24h retention (pruned by the daily cron
 * + lazy expiry here). Oracle-validated design.
 */
class IdempotencyStore extends AbstractService
{
    protected const TTL = 86400; // 24h

    /**
     * Extract the idempotency key from the request: Idempotency-Key header wins,
     * else an idempotency_key body field. Returns null when neither is present.
     */
    public function extractKey(array $requestData): ?string
    {
        $header = $this->app->request()->getServer('HTTP_IDEMPOTENCY_KEY');
        if (is_string($header) && $header !== '') {
            return $this->sanitizeKey($header);
        }
        $body = $requestData['idempotency_key'] ?? null;
        if (is_string($body) && $body !== '') {
            return $this->sanitizeKey($body);
        }
        return null;
    }

    protected function sanitizeKey(string $key): ?string
    {
        // Printable ASCII, max 255. Reject anything else (no UUID format required).
        $key = substr($key, 0, 255);
        return preg_match('/^[\x20-\x7E]+$/', $key) ? $key : null;
    }

    /**
     * Raw sha256 over module|tool|canonical(args) — detects key reuse with a
     * different body.
     */
    public function requestHash(string $module, string $tool, array $args): string
    {
        return hash('sha256', $module . '|' . $tool . '|' . $this->canonicalJson($args), true);
    }

    /**
     * Try to claim the key. Returns one of:
     *   ['action' => 'proceed',  'id' => int]      -> caller owns it, execute
     *   ['action' => 'replay',   'response' => []]  -> return cached success
     *   ['action' => 'conflict']                     -> 409, first request in flight
     *   ['action' => 'mismatch']                     -> 422, key reused w/ diff body
     */
    public function reserve(int $userId, string $key, string $requestHash, string $tool): array
    {
        $db = $this->app->db();
        $now = \XF::$time;
        $expires = $now + self::TTL;

        // Atomic claim: the UNIQUE (user_id, key) index is the lock.
        $db->query(
            'INSERT IGNORE INTO xf_chgold_aiconnect_idempotency
                (user_id, idempotency_key, request_hash, tool, status, created_date, expires_date)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$userId, $key, $requestHash, substr($tool, 0, 100), 'pending', $now, $expires]
        );
        $insertId = (int) $db->lastInsertId();
        if ($insertId > 0) {
            return ['action' => 'proceed', 'id' => $insertId];
        }

        // Row already exists — inspect it.
        $existing = $db->fetchRow(
            'SELECT idempotency_id, request_hash, status, response_json, expires_date
               FROM xf_chgold_aiconnect_idempotency
              WHERE user_id = ? AND idempotency_key = ?',
            [$userId, $key]
        );
        if (!$existing) {
            // Extremely rare race (row deleted between INSERT IGNORE and SELECT).
            // Retry the claim once.
            return $this->reserve($userId, $key, $requestHash, $tool);
        }

        // Expired -> delete + re-claim fresh.
        if ((int) $existing['expires_date'] < $now) {
            $db->delete('xf_chgold_aiconnect_idempotency', 'idempotency_id = ?', $existing['idempotency_id']);
            return $this->reserve($userId, $key, $requestHash, $tool);
        }

        // Different body under the same key -> client bug.
        if (!hash_equals((string) $existing['request_hash'], $requestHash)) {
            return ['action' => 'mismatch'];
        }

        // Same body, still running -> in flight.
        if ($existing['status'] === 'pending') {
            return ['action' => 'conflict'];
        }

        // Same body, completed -> replay the cached success.
        $decoded = json_decode((string) $existing['response_json'], true);
        return ['action' => 'replay', 'response' => is_array($decoded) ? $decoded : ['success' => true]];
    }

    /**
     * Mark a reserved row completed + cache the success result for replay.
     */
    public function complete(int $id, array $result): void
    {
        $this->app->db()->update(
            'xf_chgold_aiconnect_idempotency',
            [
                'status'         => 'completed',
                'response_json'  => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'completed_date' => \XF::$time,
            ],
            'idempotency_id = ?',
            $id
        );
    }

    /**
     * Release a reserved row (failure/exception) so a retry can re-attempt.
     */
    public function release(int $id): void
    {
        $this->app->db()->delete('xf_chgold_aiconnect_idempotency', 'idempotency_id = ?', $id);
    }

    /**
     * Retention prune (called by the daily cron). Removes expired keys.
     */
    public static function prune(): void
    {
        \XF::db()->delete('xf_chgold_aiconnect_idempotency', 'expires_date < ?', \XF::$time);
    }

    /**
     * Recursively key-sorted JSON so the same logical args hash identically
     * regardless of field order.
     */
    protected function canonicalJson($data): string
    {
        $canon = $this->canonicalize($data);
        return (string) json_encode($canon, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    protected function canonicalize($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        $isAssoc = array_keys($data) !== range(0, count($data) - 1);
        if ($isAssoc) {
            ksort($data);
        }
        foreach ($data as $k => $v) {
            $data[$k] = $this->canonicalize($v);
        }
        return $data;
    }
}
