<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Audit-log analytics tools (admin, read-only).
 *
 * Lets the AI analyse the AI Connect action log (xf_chgold_aiconnect_action_log,
 * owned by the Core addon) FOR the admin — "who is most active?", "what is
 * failing?", "is there unusual activity?", "what did user X do?". All tools are
 * read-only aggregate queries; they never mutate the log and never surface the
 * raw masked args unless explicitly asked.
 *
 * Gated on the admin 'option' permission (same as the audit-log ACP page).
 */
trait AdminAnalyticsTrait
{
    protected function registerAnalyticsTools(): void
    {
        $this->registerTool('analyzeAuditActivity', [
            'description' => 'Overview of AI Connect action-log activity for a period: totals, '
                . 'failure rate, read/write split, top tools, top users, daily volume. '
                . 'Use to answer "how is the AI being used / by whom / what is failing".',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'days'  => ['type' => 'integer', 'description' => 'Look-back window in days (default 30, max 365).'],
                    'limit' => ['type' => 'integer', 'description' => 'Top-N for tools/users lists (default 10, max 50).'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getAuditFailures', [
            'description' => 'Recent FAILED tool calls from the action log (tool, user, error_code, when). '
                . 'Use to answer "what is breaking / which tools fail most".',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'days'  => ['type' => 'integer', 'description' => 'Look-back window in days (default 7).'],
                    'limit' => ['type' => 'integer', 'description' => 'Max rows (default 25, max 100).'],
                    'tool'  => ['type' => 'string', 'description' => 'Optional: only this tool (short or module.tool).'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getUserAuditActivity', [
            'description' => 'A single user\'s AI Connect activity: counts, failure rate, their top tools, '
                . 'first/last action. Use to answer "what has user X been doing via the AI".',
            'input_schema' => [
                'type' => 'object',
                'required' => ['user_id'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'The XF user id to analyse.'],
                    'days'    => ['type' => 'integer', 'description' => 'Look-back window in days (default 30).'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('detectAuditAnomalies', [
            'description' => 'Flags unusual AI Connect activity in a period: users with a high failure rate, '
                . 'tools failing far more than average, and day-over-day volume spikes. '
                . 'Use to answer "is there anything suspicious / abnormal in the AI usage".',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'days' => ['type' => 'integer', 'description' => 'Look-back window in days (default 30).'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    // ---- handlers -----------------------------------------------------------

    protected function auditAnalyticsGuard()
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        return $this->assertPermission('option');
    }

    protected function auditSince(array $params, int $default): int
    {
        $days = (int) ($params['days'] ?? $default);
        $days = max(1, min(365, $days));
        return \XF::$time - ($days * 86400);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function execute_analyzeAuditActivity($params)
    {
        if ($err = $this->auditAnalyticsGuard()) {
            return $err;
        }
        $db = \XF::db();
        $since = $this->auditSince($params, 30);
        $limit = max(1, min(50, (int) ($params['limit'] ?? 10)));

        $totals = $db->fetchRow(
            'SELECT COUNT(*) total, COALESCE(SUM(success=0),0) failures,
                    COALESCE(SUM(http_method=\'read\'),0) `reads`,
                    COALESCE(SUM(http_method=\'write\'),0) `writes`
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?',
            [$since]
        );
        $total = (int) $totals['total'];
        $failures = (int) $totals['failures'];

        $topTools = $db->fetchAll(
            'SELECT module, tool, COUNT(*) calls, COALESCE(SUM(success=0),0) fails
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?
              GROUP BY module, tool ORDER BY calls DESC LIMIT ' . $limit,
            [$since]
        );
        $topUsers = $db->fetchAll(
            'SELECT user_id, username, COUNT(*) actions, COALESCE(SUM(success=0),0) fails
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?
              GROUP BY user_id, username ORDER BY actions DESC LIMIT ' . $limit,
            [$since]
        );
        $daily = $db->fetchAll(
            'SELECT FLOOR(log_date/86400)*86400 day, COUNT(*) actions
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?
              GROUP BY day ORDER BY day',
            [$since]
        );

        return $this->success([
            'period_days'  => (int) (($since > 0) ? round((\XF::$time - $since) / 86400) : 0),
            'total'        => $total,
            'failures'     => $failures,
            'failure_rate' => $total > 0 ? round(($failures / $total) * 100, 1) : 0.0,
            'reads'        => (int) $totals['reads'],
            'writes'       => (int) $totals['writes'],
            'top_tools'    => array_map(static fn ($r) => [
                'tool'  => $r['module'] . '.' . $r['tool'],
                'calls' => (int) $r['calls'],
                'fails' => (int) $r['fails'],
            ], $topTools),
            'top_users'    => array_map(static fn ($r) => [
                'user_id'  => (int) $r['user_id'],
                'username' => $r['username'],
                'actions'  => (int) $r['actions'],
                'fails'    => (int) $r['fails'],
            ], $topUsers),
            'daily_volume' => array_map(static fn ($r) => [
                'date'    => gmdate('Y-m-d', (int) $r['day']),
                'actions' => (int) $r['actions'],
            ], $daily),
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function execute_getAuditFailures($params)
    {
        if ($err = $this->auditAnalyticsGuard()) {
            return $err;
        }
        $db = \XF::db();
        $since = $this->auditSince($params, 7);
        $limit = max(1, min(100, (int) ($params['limit'] ?? 25)));

        $where = ['log_date >= ?', 'success = 0'];
        $args = [$since];
        if (!empty($params['tool'])) {
            $t = (string) $params['tool'];
            if (strpos($t, '.') !== false) {
                [$m, $tn] = explode('.', $t, 2);
                $where[] = 'module = ?';
                $args[] = $m;
                $where[] = 'tool = ?';
                $args[] = $tn;
            } else {
                $where[] = 'tool = ?';
                $args[] = $t;
            }
        }
        $rows = $db->fetchAll(
            'SELECT log_date, user_id, username, module, tool, http_method, error_code
               FROM xf_chgold_aiconnect_action_log
              WHERE ' . implode(' AND ', $where) . '
              ORDER BY log_id DESC LIMIT ' . $limit,
            $args
        );
        return $this->success([
            'count'    => count($rows),
            'failures' => array_map(static fn ($r) => [
                'date'       => gmdate('Y-m-d H:i:s', (int) $r['log_date']),
                'user_id'    => (int) $r['user_id'],
                'username'   => $r['username'],
                'tool'       => $r['module'] . '.' . $r['tool'],
                'method'     => $r['http_method'],
                'error_code' => $r['error_code'],
            ], $rows),
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function execute_getUserAuditActivity($params)
    {
        if ($err = $this->auditAnalyticsGuard()) {
            return $err;
        }
        $db = \XF::db();
        $since = $this->auditSince($params, 30);
        $userId = (int) $params['user_id'];

        $summary = $db->fetchRow(
            'SELECT COUNT(*) total, COALESCE(SUM(success=0),0) failures,
                    MIN(log_date) first_date, MAX(log_date) last_date, MAX(username) username
               FROM xf_chgold_aiconnect_action_log WHERE user_id = ? AND log_date >= ?',
            [$userId, $since]
        );
        $total = (int) $summary['total'];
        $topTools = $db->fetchAll(
            'SELECT module, tool, COUNT(*) calls
               FROM xf_chgold_aiconnect_action_log WHERE user_id = ? AND log_date >= ?
              GROUP BY module, tool ORDER BY calls DESC LIMIT 15',
            [$userId, $since]
        );
        return $this->success([
            'user_id'      => $userId,
            'username'     => $summary['username'],
            'total'        => $total,
            'failures'     => (int) $summary['failures'],
            'failure_rate' => $total > 0 ? round(((int) $summary['failures'] / $total) * 100, 1) : 0.0,
            'first_action' => $summary['first_date'] ? gmdate('Y-m-d H:i:s', (int) $summary['first_date']) : null,
            'last_action'  => $summary['last_date'] ? gmdate('Y-m-d H:i:s', (int) $summary['last_date']) : null,
            'top_tools'    => array_map(static fn ($r) => [
                'tool'  => $r['module'] . '.' . $r['tool'],
                'calls' => (int) $r['calls'],
            ], $topTools),
        ]);
    }

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function execute_detectAuditAnomalies($params)
    {
        if ($err = $this->auditAnalyticsGuard()) {
            return $err;
        }
        $db = \XF::db();
        $since = $this->auditSince($params, 30);

        // Users with a high failure rate (>=30%) over >=10 actions.
        $riskUsers = $db->fetchAll(
            'SELECT user_id, username, COUNT(*) actions, COALESCE(SUM(success=0),0) fails
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?
              GROUP BY user_id, username
              HAVING COUNT(*) >= 10 AND COALESCE(SUM(success=0),0) / COUNT(*) >= 0.30
              ORDER BY COALESCE(SUM(success=0),0) / COUNT(*) DESC LIMIT 20',
            [$since]
        );
        // Tools failing much more than the global rate (>=40% over >=10 calls).
        $riskTools = $db->fetchAll(
            'SELECT module, tool, COUNT(*) calls, COALESCE(SUM(success=0),0) fails
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?
              GROUP BY module, tool
              HAVING COUNT(*) >= 10 AND COALESCE(SUM(success=0),0) / COUNT(*) >= 0.40
              ORDER BY COALESCE(SUM(success=0),0) / COUNT(*) DESC LIMIT 20',
            [$since]
        );
        // Day-over-day volume spikes: a day with >3x the median daily volume.
        $daily = $db->fetchAllColumn(
            'SELECT COUNT(*) c FROM xf_chgold_aiconnect_action_log
              WHERE log_date >= ? GROUP BY FLOOR(log_date/86400)',
            [$since]
        );
        sort($daily);
        $median = $daily ? (int) $daily[intdiv(count($daily), 2)] : 0;
        $spikeDays = [];
        if ($median > 0) {
            $rows = $db->fetchAll(
                'SELECT FLOOR(log_date/86400)*86400 day, COUNT(*) actions
                   FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?
                  GROUP BY day HAVING actions > ? ORDER BY actions DESC',
                [$since, $median * 3]
            );
            foreach ($rows as $r) {
                $spikeDays[] = ['date' => gmdate('Y-m-d', (int) $r['day']), 'actions' => (int) $r['actions']];
            }
        }

        return $this->success([
            'high_failure_users' => array_map(static fn ($r) => [
                'user_id'      => (int) $r['user_id'],
                'username'     => $r['username'],
                'actions'      => (int) $r['actions'],
                'fails'        => (int) $r['fails'],
                'failure_rate' => round(((int) $r['fails'] / max(1, (int) $r['actions'])) * 100, 1),
            ], $riskUsers),
            'high_failure_tools' => array_map(static fn ($r) => [
                'tool'         => $r['module'] . '.' . $r['tool'],
                'calls'        => (int) $r['calls'],
                'fails'        => (int) $r['fails'],
                'failure_rate' => round(((int) $r['fails'] / max(1, (int) $r['calls'])) * 100, 1),
            ], $riskTools),
            'volume_spike_days'  => $spikeDays,
            'median_daily_volume' => $median,
        ]);
    }
}
