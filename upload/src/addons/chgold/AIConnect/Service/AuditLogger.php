<?php

namespace chgold\AIConnect\Service;

use XF\Service\AbstractService;

/**
 * Action-level audit logger (roadmap item 7).
 *
 * One row per tool call, written synchronously from the single Tools.php
 * choke-point so it covers Core + Pro + Admin + Moderation + any future add-on.
 * The insert is a single narrow indexed row (~700 bytes) — negligible next to
 * the AI tool call it records.
 *
 * SECRETS ARE NEVER PERSISTED: args + result are masked (key-name denylist,
 * recursive, depth-bounded) and truncated to 500 chars before storage.
 */
class AuditLogger extends AbstractService
{
    /**
     * Keys whose VALUES are redacted before an args/result summary is stored.
     * Tool authors: name any secret-carrying field with one of these terms.
     */
    protected const SECRET_KEY_PATTERN = '/(password|passwd|secret|token|api[_-]?key|smtp|auth|bearer|credential)/i';

    /**
     * Result keys that identify affected content, mapped to a content_type.
     * Best-effort: covers the common XF shapes without per-tool declaration.
     */
    protected const CONTENT_ID_KEYS = [
        'thread_id'       => 'thread',
        'post_id'         => 'post',
        'user_id'         => 'user',
        'node_id'         => 'node',
        'conversation_id' => 'conversation',
        'message_id'      => 'conversation_message',
        'attachment_id'   => 'attachment',
        'warning_id'      => 'warning',
        'report_id'       => 'report',
        'profile_post_id' => 'profile_post',
    ];

    /**
     * Log one tool call. Never throws — audit failure must not break the call.
     *
     * @param array $data user_id, username, client_id, module, tool,
     *                    http_method('read'|'write'), input(array), result(array),
     *                    duration_ms(int), ip(string)
     */
    public function log(array $data): void
    {
        try {
            $result = is_array($data['result'] ?? null) ? $data['result'] : [];
            $input  = is_array($data['input'] ?? null) ? $data['input'] : [];

            $success = !(isset($result['success']) && $result['success'] === false);
            $errorCode = '';
            if (!$success) {
                $errorCode = (string) ($result['error']['code'] ?? $result['error_code'] ?? 'error');
            }

            [$contentType, $contentId] = $this->extractContent($result);

            \XF::db()->insert('xf_chgold_aiconnect_action_log', [
                'log_date'         => \XF::$time,
                'user_id'          => (int) ($data['user_id'] ?? 0),
                'username'         => substr((string) ($data['username'] ?? ''), 0, 50),
                'client_id'        => substr((string) ($data['client_id'] ?? ''), 0, 80),
                'module'           => substr((string) ($data['module'] ?? ''), 0, 50),
                'tool'             => substr((string) ($data['tool'] ?? ''), 0, 75),
                'http_method'      => ($data['http_method'] ?? 'read') === 'write' ? 'write' : 'read',
                'success'          => $success ? 1 : 0,
                'error_code'       => substr($errorCode, 0, 50),
                'duration_ms'      => max(0, (int) ($data['duration_ms'] ?? 0)),
                'ip_address'       => substr((string) ($data['ip'] ?? ''), 0, 45),
                'content_type'     => $contentType,
                'content_id'       => $contentId,
                'request_summary'  => $this->summarize($input),
                'response_summary' => $this->summarize($result),
            ]);
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'AIConnect AuditLogger::log failed: ');
        }
    }

    /**
     * Best-effort affected-content extraction from a tool result.
     * Returns [content_type, content_id]; [ '', 0 ] when nothing identifiable.
     */
    protected function extractContent(array $result): array
    {
        // Tool results commonly wrap payload under 'data'.
        $scan = is_array($result['data'] ?? null) ? $result['data'] : $result;
        foreach (self::CONTENT_ID_KEYS as $key => $type) {
            if (isset($scan[$key]) && (is_int($scan[$key]) || ctype_digit((string) $scan[$key]))) {
                return [$type, (int) $scan[$key]];
            }
        }
        return ['', 0];
    }

    /**
     * Mask secrets, JSON-encode, truncate to 500 chars for storage.
     */
    protected function summarize(array $data): string
    {
        $json = json_encode($this->mask($data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return '';
        }
        return strlen($json) > 500 ? substr($json, 0, 497) . '...' : $json;
    }

    /**
     * Recursively redact values under secret-looking keys. Depth-bounded to
     * keep the hot path cheap and to guard against pathological nesting.
     */
    protected function mask(array $data, int $depth = 0): array
    {
        if ($depth > 3) {
            return ['__truncated__' => true];
        }
        foreach ($data as $k => $v) {
            if (preg_match(self::SECRET_KEY_PATTERN, (string) $k)) {
                $data[$k] = '***REDACTED***';
            } elseif (is_array($v)) {
                $data[$k] = $this->mask($v, $depth + 1);
            }
        }
        return $data;
    }

    /**
     * Retention prune (called by the daily cron). No-op when retention = 0.
     */
    public static function prune(): void
    {
        $days = (int) (\XF::options()->aiconnect_audit_retention_days ?? 90);
        if ($days <= 0) {
            return;
        }
        $cutoff = \XF::$time - ($days * 86400);
        \XF::db()->delete('xf_chgold_aiconnect_action_log', 'log_date < ?', $cutoff);
    }
}
