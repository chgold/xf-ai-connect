<?php

namespace chgold\AIConnect\Cron;

/**
 * Daily prune of AI Connect housekeeping tables:
 *   - action-level audit log per the retention option (roadmap item 7)
 *   - expired idempotency keys (roadmap item 6, 24h TTL)
 * Each delegate is a no-op when there is nothing to prune.
 */
class AuditPrune
{
    public static function run(): void
    {
        \chgold\AIConnect\Service\AuditLogger::prune();
        \chgold\AIConnect\Service\IdempotencyStore::prune();
    }
}
