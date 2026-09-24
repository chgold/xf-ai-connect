<?php

namespace chgold\AIConnect\Cron;

/**
 * Daily prune of the action-level audit log per the retention option
 * (roadmap item 7). Delegates to AuditLogger::prune() (no-op when retention=0).
 */
class AuditPrune
{
    public static function run(): void
    {
        \chgold\AIConnect\Service\AuditLogger::prune();
    }
}
