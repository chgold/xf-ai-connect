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

        // Item ג: default the date window to the last 7 days so the page never
        // dumps the whole (potentially huge) log on first load. A sentinel flag
        // ('filtered') is set once the admin submits the form, so an explicit
        // empty 'from' (clearing the date) is respected instead of re-defaulting.
        // The 'to' end of the window defaults to today (item 1) so both date
        // fields are pre-filled with a sensible last-week..today range.
        if (!$this->filter('filtered', 'bool')) {
            if ($filters['from'] === '') {
                $filters['from'] = date('Y-m-d', \XF::$time - (7 * 86400));
            }
            if ($filters['to'] === '') {
                $filters['to'] = date('Y-m-d', \XF::$time);
            }
        }

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

        // Item 2: preformat the full timestamp WITH seconds in the board timezone.
        // XF's date()/dateTime() helpers never emit seconds (and show "Today at
        // 1:02 PM" relative form), so audit rows lost precision. We render an
        // explicit YYYY-MM-DD HH:MM:SS here so every row shows the exact instant.
        $tz = new \DateTimeZone(\XF::visitor()->timezone ?: (\XF::options()->guestTimeZone ?: 'UTC'));
        foreach ($logs as &$logRow) {
            $dt = new \DateTime('@' . $logRow['log_date']);
            $dt->setTimezone($tz);
            $logRow['date_full'] = $dt->format('Y-m-d H:i:s');
        }
        unset($logRow);

        // Hierarchical filter data (item ג): module -> its distinct tools, built
        // from what is actually in the log (cheap; small cardinality). The
        // template renders module + tool dropdowns; the tool list is filtered
        // client-side by the selected module via a data-module attribute.
        $pairs = $db->fetchAll(
            "SELECT DISTINCT module, tool FROM xf_chgold_aiconnect_action_log ORDER BY module, tool"
        );
        $modules = [];
        $toolsByModule = [];
        foreach ($pairs as $p) {
            $modules[$p['module']] = true;
            $toolsByModule[$p['module']][] = $p['tool'];
        }
        $modules = array_keys($modules);

        $viewParams = [
            'logs'          => $logs,
            'total'         => $total,
            'page'          => $page,
            'perPage'       => $perPage,
            'filters'       => $filters,
            'modules'       => $modules,
            'toolsByModule' => $toolsByModule,
        ];
        return $this->view('chgold\AIConnect:AuditLog\List', 'chgold_aiconnect_audit_list', $viewParams);
    }

    /**
     * Item 4: purge audit rows older than an admin-chosen date. GET renders a
     * confirm form (with a preview count); POST performs the delete. This is on
     * top of the automatic retention cron — a manual "clear everything before
     * date X" control. Destructive, so POST-only + explicit confirmation.
     */
    public function actionDelete()
    {
        $db = \XF::db();

        if ($this->isPost()) {
            $before = $this->filter('before', 'str');
            $ts = $before !== '' ? strtotime($before) : false;
            if ($ts === false) {
                return $this->error('Please enter a valid date.');
            }
            // A bare date means "delete everything up to the END of that day".
            if (!preg_match('/\d:\d/', $before)) {
                $ts = strtotime('+1 day -1 second', $ts);
            }
            $deleted = $db->delete('xf_chgold_aiconnect_action_log', 'log_date <= ?', $ts);

            return $this->redirect(
                $this->buildLink('ai-connect/audit-log'),
                \XF::phrase('aiconnect_audit_deleted_x', ['count' => $deleted])
            );
        }

        // GET: confirm form. Default the cutoff to 30 days ago + show how many
        // rows would be removed so the admin sees the impact before confirming.
        $default = date('Y-m-d', \XF::$time - (30 * 86400));
        $cutoffTs = strtotime('+1 day -1 second', strtotime($default));
        $affected = (int) $db->fetchOne(
            'SELECT COUNT(*) FROM xf_chgold_aiconnect_action_log WHERE log_date <= ?',
            [$cutoffTs]
        );
        $totalRows = (int) $db->fetchOne('SELECT COUNT(*) FROM xf_chgold_aiconnect_action_log');

        return $this->view('chgold\AIConnect:AuditLog\Delete', 'chgold_aiconnect_audit_delete', [
            'default'   => $default,
            'affected'  => $affected,
            'totalRows' => $totalRows,
        ]);
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
            // A bare date ("2026-09-24") parses to 00:00:00, which would exclude
            // everything logged that same day. Extend a time-less 'to' to the end
            // of that day so "to = today" includes today's events.
            if (!preg_match('/\d:\d/', $filters['to'])) {
                $ts = strtotime('+1 day -1 second', $ts);
            }
            $where[] = 'log_date <= ?';
            $params[] = $ts;
        }

        return [$where, $params];
    }
}
