<?php

namespace chgold\AIConnect\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

/**
 * Action-level audit log admin UI (roadmap item 7).
 *
 * A filterable list of every AI tool call (user / tool / module / date /
 * success) + a statistics panel (totals, failure rate, top tools, daily
 * volume). Read-only; the log is written by Service\AuditLogger at the
 * Tools.php choke-point. Reads directly from xf_chgold_aiconnect_action_log
 * (no Entity — the table is append-only + display-only).
 */
class AuditLog extends AbstractController
{
    protected function preDispatchController($action, ParameterBag $params): void
    {
        $this->assertAdminPermission('option');
    }

    public function actionIndex()
    {
        $page = $this->filterPage();
        $perPage = 50;

        $filters = $this->filter([
            'user_id' => 'uint',
            'username' => 'str',
            'module'  => 'str',
            'tool'    => 'str',
            'method'  => 'str',
            'result'  => 'str', // '', 'success', 'fail'
            'from'    => 'str',
            'to'      => 'str',
        ]);

        [$where, $params] = $this->buildWhere($filters);
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $db = \XF::db();
        $total = (int) $db->fetchOne(
            "SELECT COUNT(*) FROM xf_chgold_aiconnect_action_log $whereSql",
            $params
        );
        $logs = $db->fetchAll(
            "SELECT * FROM xf_chgold_aiconnect_action_log
             $whereSql
             ORDER BY log_id DESC
             LIMIT " . (($page - 1) * $perPage) . ", $perPage",
            $params
        );

        // Distinct modules/tools for the filter dropdowns (cheap; small cardinality).
        $modules = $db->fetchAllColumn(
            "SELECT DISTINCT module FROM xf_chgold_aiconnect_action_log ORDER BY module"
        );

        $viewParams = [
            'logs'    => $logs,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'filters' => $filters,
            'modules' => $modules,
        ];
        return $this->view('chgold\AIConnect:AuditLog\List', 'chgold_aiconnect_audit_list', $viewParams);
    }

    public function actionStats()
    {
        $db = \XF::db();
        $days = 30;
        $since = \XF::$time - ($days * 86400);

        $totals = $db->fetchRow(
            "SELECT COUNT(*) AS total, COALESCE(SUM(success = 0), 0) AS failures
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?",
            [$since]
        );
        $total = (int) ($totals['total'] ?? 0);
        $failures = (int) ($totals['failures'] ?? 0);
        $failureRate = $total > 0 ? round(($failures / $total) * 100, 1) : 0.0;

        $topTools = $db->fetchAll(
            "SELECT module, tool, COUNT(*) AS c,
                    COALESCE(SUM(success = 0), 0) AS fails
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?
              GROUP BY module, tool ORDER BY c DESC LIMIT 15",
            [$since]
        );

        $topUsers = $db->fetchAll(
            "SELECT user_id, username, COUNT(*) AS c
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?
              GROUP BY user_id, username ORDER BY c DESC LIMIT 10",
            [$since]
        );

        $dailyRaw = $db->fetchAll(
            "SELECT FLOOR(log_date / 86400) * 86400 AS day, COUNT(*) AS c
               FROM xf_chgold_aiconnect_action_log WHERE log_date >= ?
              GROUP BY day ORDER BY day",
            [$since]
        );
        $maxDaily = 0;
        foreach ($dailyRaw as $d) {
            $maxDaily = max($maxDaily, (int) $d['c']);
        }

        $readWrite = $db->fetchPairs(
            "SELECT http_method, COUNT(*) FROM xf_chgold_aiconnect_action_log
              WHERE log_date >= ? GROUP BY http_method",
            [$since]
        );

        return $this->view('chgold\AIConnect:AuditLog\Stats', 'chgold_aiconnect_audit_stats', [
            'days'        => $days,
            'total'       => $total,
            'failures'    => $failures,
            'failureRate' => $failureRate,
            'reads'       => (int) ($readWrite['read'] ?? 0),
            'writes'      => (int) ($readWrite['write'] ?? 0),
            'topTools'    => $topTools,
            'topUsers'    => $topUsers,
            'daily'       => $dailyRaw,
            'maxDaily'    => $maxDaily,
        ]);
    }

    /**
     * Build the WHERE fragments + bound params from the filter set.
     *
     * @return array{0: string[], 1: array}
     */
    protected function buildWhere(array $filters): array
    {
        $where = [];
        $params = [];

        if ($filters['user_id']) {
            $where[] = 'user_id = ?';
            $params[] = $filters['user_id'];
        }
        if ($filters['username'] !== '') {
            $where[] = 'username = ?';
            $params[] = $filters['username'];
        }
        if ($filters['module'] !== '') {
            $where[] = 'module = ?';
            $params[] = $filters['module'];
        }
        if ($filters['tool'] !== '') {
            $where[] = 'tool = ?';
            $params[] = $filters['tool'];
        }
        if ($filters['method'] === 'read' || $filters['method'] === 'write') {
            $where[] = 'http_method = ?';
            $params[] = $filters['method'];
        }
        if ($filters['result'] === 'success') {
            $where[] = 'success = 1';
        } elseif ($filters['result'] === 'fail') {
            $where[] = 'success = 0';
        }
        if ($filters['from'] !== '' && ($ts = strtotime($filters['from'])) !== false) {
            $where[] = 'log_date >= ?';
            $params[] = $ts;
        }
        if ($filters['to'] !== '' && ($ts = strtotime($filters['to'])) !== false) {
            $where[] = 'log_date <= ?';
            $params[] = $ts;
        }

        return [$where, $params];
    }
}
