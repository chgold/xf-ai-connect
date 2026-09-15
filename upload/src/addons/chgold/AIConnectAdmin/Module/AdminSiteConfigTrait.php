<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Site config bundle: options discovery + email transport + addon options.
 * Group A: listOptionsByGroup, getOptionBlocklist
 * Group B: getEmailTransportConfig/setEmailTransportConfig/testEmailConfig
 *          + getAddonOptions/setAddonOption
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminSiteConfigTrait
{
    // Same blocklist as AdminOptionsTrait — exposed via getOptionBlocklist
    protected const OPTION_BLOCKLIST = [
        'boardActive', 'boardUrl', 'homePageUrl',
    ];

    protected function registerSiteConfigTools()
    {
        $this->registerTool('listOptionsByGroup', [
            'description' => 'List all options in a specific option group (e.g. email, payment, '
                . '<addon_id>). Helps discover valid option_id values without guessing.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['group_id'],
                'properties' => [
                    'group_id' => ['type' => 'string', 'description' => 'Option group id — see listOptionGroups helper'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('listOptionGroups', [
            'description' => 'List all option groups defined in this XF installation.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getOptionBlocklist', [
            'description' => 'Return the list of option_ids blocked from setOption '
                . '(site-locking keys like boardActive, boardUrl, homePageUrl).',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getEmailTransportConfig', [
            'description' => 'Read the site email transport configuration. Password field is '
                . 'always MASKED (e.g. "****1234") — never returned in plain text.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('setEmailTransportConfig', [
            'description' => 'Set email transport (smtp/default). v1.4.11: writes XF native option keys '
                . '(smtpHost/smtpPort/smtpSsl/smtpLoginUsername/smtpLoginPassword/smtpAuth) so XF Mailer '
                . 'can consume without crashing (bug in v1.4.0-v1.4.10: missing smtpSsl key caused '
                . '"Undefined array key smtpSsl" on any mail send, including createUser welcome mail). '
                . 'Recommended: call testEmailConfig after.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['transport'],
                'properties' => [
                    'transport' => ['type' => 'string', 'enum' => ['default', 'smtp']],
                    'from_email' => ['type' => 'string'],
                    'from_name' => ['type' => 'string'],
                    // SMTP-specific
                    'smtp_host' => ['type' => 'string'],
                    'smtp_port' => ['type' => 'integer'],
                    'smtp_encryption' => ['type' => 'string', 'enum' => ['', 'ssl', 'tls'], 'description' => 'ssl→smtpSsl=true, tls or empty→false'],
                    'smtp_auth' => ['type' => 'string', 'enum' => ['', 'login', 'plain'], 'description' => 'SMTP auth method (default: login)'],
                    'smtp_username' => ['type' => 'string'],
                    'smtp_password' => ['type' => 'string', 'description' => 'Plain password (stored server-side, never returned)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('testEmailConfig', [
            'description' => 'Send a test email using the current transport config. '
                . 'Verifies connectivity before saving password changes.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['to_email'],
                'properties' => [
                    'to_email' => ['type' => 'string'],
                    'subject' => ['type' => 'string'],
                    'body' => ['type' => 'string'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getAddonOptions', [
            'description' => 'List all options belonging to a specific add-on (by addon_id).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['addon_id'],
                'properties' => ['addon_id' => ['type' => 'string']],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('setAddonOption', [
            'description' => 'Set a single option belonging to an add-on. Same as setOption but '
                . 'requires addon_id and refuses if the option is not from that add-on '
                . '(prevents accidentally modifying core options).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['addon_id', 'option_id', 'value'],
                'properties' => [
                    'addon_id' => ['type' => 'string'],
                    'option_id' => ['type' => 'string'],
                    'value' => ['description' => 'New option value'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────

    public function execute_listOptionsByGroup($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('option')) return $err;

        $group = (string) $params['group_id'];
        $rows = \XF::db()->fetchAll(
            'SELECT o.option_id, o.data_type, o.default_value, o.addon_id, r.option_value
             FROM xf_option o
             INNER JOIN xf_option_group_relation r_rel ON r_rel.option_id = o.option_id
             LEFT JOIN xf_option r ON r.option_id = o.option_id
             WHERE r_rel.group_id = ?
             ORDER BY r_rel.display_order, o.option_id',
            [$group]
        );

        $blocked = self::OPTION_BLOCKLIST;
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'option_id'  => $r['option_id'],
                'data_type'  => $r['data_type'],
                'value'      => $r['option_value'] ?? $r['default_value'],
                'addon_id'   => $r['addon_id'],
                'writable'   => !in_array($r['option_id'], $blocked, true),
            ];
        }
        return $this->success(['group_id' => $group, 'count' => count($out), 'options' => $out]);
    }

    public function execute_listOptionGroups($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('option')) return $err;

        // xf_option_group has no title column — titles live in phrases:
        // "option_group.{group_id}". Fetch via LEFT JOIN.
        $rows = \XF::db()->fetchAll(
            "SELECT g.group_id, g.display_order, g.debug_only, g.addon_id,
                    p.phrase_text AS title
             FROM xf_option_group g
             LEFT JOIN xf_phrase p ON p.title = CONCAT('option_group.', g.group_id)
                                   AND p.language_id = 0
             ORDER BY g.display_order, g.group_id"
        );
        return $this->success(['count' => count($rows), 'groups' => $rows]);
    }

    public function execute_getOptionBlocklist($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        return $this->success([
            'blocked_option_ids' => self::OPTION_BLOCKLIST,
            'reason' => 'These option_ids can lock the site out if set incorrectly. '
                . 'Use ACP directly to change them.',
        ]);
    }

    public function execute_getEmailTransportConfig($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('option')) return $err;

        // XF 2.3 native emailTransport option structure (see XF\Admin\Controller\OptionController
        // ::actionEmailTransport lines 640-655) is an ARRAY with these keys:
        //   emailTransport (str), smtpHost (str), smtpPort (uint), smtpAuth (str),
        //   smtpLoginUsername (str), smtpLoginPassword (str), smtpSsl (bool)
        // XF\Mail\Mailer.php lines 369+377 CRASHES with 'Undefined array key smtpSsl'
        // if the config lacks smtpSsl — v1.4.10 setEmailTransportConfig stored
        // {host,port,encryption,username,password} instead of XF native keys, causing
        // 'createUser + SMTP transport' to break (welcome mail send fails). v1.4.11
        // stores XF native keys ONLY.
        $opts = \XF::options();
        $config = self::normalizeEmailTransportConfig($opts->emailTransport ?? null);
        $transport = (string) ($config['emailTransport'] ?? 'default');

        $pwd = (string) ($config['smtpLoginPassword'] ?? '');
        $maskedPwd = $pwd === '' ? '' : '****' . substr($pwd, -4);

        return $this->success([
            'transport'  => $transport,
            'from_email' => $opts->defaultEmailAddress ?? '',
            'from_name'  => $opts->emailSenderName ?? '',
            'smtp' => [
                'host'       => (string) ($config['smtpHost'] ?? ''),
                'port'       => (int)    ($config['smtpPort'] ?? 0),
                'encryption' => ($config['smtpSsl'] ?? false) ? 'ssl' : ($config['smtpPort'] === 587 ? 'tls' : ''),
                'auth'       => (string) ($config['smtpAuth'] ?? ''),
                'username'   => (string) ($config['smtpLoginUsername'] ?? ''),
                'password'   => $maskedPwd,
                'smtpSsl'    => (bool)   ($config['smtpSsl'] ?? false),
            ],
        ]);
    }

    public function execute_setEmailTransportConfig($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('option')) return $err;

        // v1.4.11: store with XF NATIVE keys (smtpHost, smtpPort, smtpAuth,
        // smtpLoginUsername, smtpLoginPassword, smtpSsl). Previous versions
        // stored bare {host, port, encryption, username, password} which
        // XF\Mail\Mailer.php cannot consume — the missing smtpSsl key crashed
        // any mail send (including welcome mail on createUser).
        // Start from a CLEAN slate (do not merge legacy wrong keys forward).
        $transport = (string) $params['transport'];
        $encryption = strtolower((string) ($params['smtp_encryption'] ?? ''));  // '', 'ssl', 'tls'

        $current = [
            'emailTransport' => $transport,
        ];

        if ($transport === 'smtp') {
            // If caller is UPDATING existing SMTP config, preserve unchanged fields
            $existing = self::normalizeEmailTransportConfig(\XF::options()->emailTransport ?? null);

            $current['smtpHost'] = isset($params['smtp_host'])
                ? (string) $params['smtp_host']
                : (string) ($existing['smtpHost'] ?? '');
            $current['smtpPort'] = isset($params['smtp_port'])
                ? (int) $params['smtp_port']
                : (int) ($existing['smtpPort'] ?? 587);
            $current['smtpAuth'] = isset($params['smtp_auth'])
                ? (string) $params['smtp_auth']
                : (string) ($existing['smtpAuth'] ?? 'login');
            $current['smtpLoginUsername'] = isset($params['smtp_username'])
                ? (string) $params['smtp_username']
                : (string) ($existing['smtpLoginUsername'] ?? '');
            $current['smtpLoginPassword'] = (isset($params['smtp_password']) && $params['smtp_password'] !== '')
                ? (string) $params['smtp_password']
                : (string) ($existing['smtpLoginPassword'] ?? '');

            // XF derives smtpSsl bool from encryption per OptionController line 721
            if ($encryption !== '') {
                $current['smtpSsl'] = ($encryption === 'ssl');
            } else {
                $current['smtpSsl'] = (bool) ($existing['smtpSsl'] ?? false);
            }
        }

        // XF 2.3: XF::app()->options() has no update(). Use Option entity + save.
        $opt = \XF::em()->find('XF:Option', 'emailTransport');
        if (!$opt) return $this->error('not_found', 'emailTransport option not found');
        $opt->option_value = $current;
        if (!$opt->save()) {
            return $this->error('validation_failed', implode(' ', $opt->getErrors()));
        }

        // Sender fields are separate string options
        if (isset($params['from_email'])) {
            $o = \XF::em()->find('XF:Option', 'defaultEmailAddress');
            if ($o) { $o->option_value = (string) $params['from_email']; $o->save(); }
        }
        if (isset($params['from_name'])) {
            $o = \XF::em()->find('XF:Option', 'emailSenderName');
            if ($o) { $o->option_value = (string) $params['from_name']; $o->save(); }
        }

        return $this->success([
            'transport'      => $transport,
            'stored_keys'    => array_keys($current),   // agent can verify XF native key names present
            'note'           => 'Config saved with XF native keys (smtpHost/smtpPort/smtpSsl/smtpLoginUsername/'
                . 'smtpLoginPassword/smtpAuth). Run testEmailConfig to verify.',
        ]);
    }

    /**
     * v1.4.11: normalizer that ACCEPTS legacy wrong-key format from v1.4.0-v1.4.10
     * ({host, port, encryption, username, password}) and returns XF-native shape.
     * Used by read-side and by write-side to seed 'existing' when merging.
     * Read-side self-heal: even if DB still holds legacy keys, callers see XF
     * native shape → 'Undefined array key smtpSsl' cannot recur once caller
     * follows through with a setEmailTransportConfig write.
     */
    private static function normalizeEmailTransportConfig($raw): array
    {
        if (!is_array($raw)) return ['emailTransport' => 'default'];

        // Already in XF native shape → return as-is (defensive on smtpSsl)
        if (isset($raw['smtpHost']) || isset($raw['smtpSsl']) || isset($raw['smtpLoginUsername'])) {
            $raw['emailTransport'] = $raw['emailTransport'] ?? 'default';
            $raw['smtpSsl'] = (bool) ($raw['smtpSsl'] ?? false);
            return $raw;
        }

        // Legacy shape (v1.4.0-v1.4.10 bug): {host, port, encryption, username, password}
        $out = ['emailTransport' => (string) ($raw['emailTransport'] ?? 'default')];
        if (isset($raw['host']))       $out['smtpHost']          = (string) $raw['host'];
        if (isset($raw['port']))       $out['smtpPort']          = (int)    $raw['port'];
        if (isset($raw['username']))   $out['smtpLoginUsername'] = (string) $raw['username'];
        if (isset($raw['password']))   $out['smtpLoginPassword'] = (string) $raw['password'];
        if (isset($raw['encryption'])) $out['smtpSsl']           = (strtolower((string) $raw['encryption']) === 'ssl');
        $out['smtpAuth'] = 'login';
        $out['smtpSsl'] = (bool) ($out['smtpSsl'] ?? false);
        return $out;
    }

    public function execute_testEmailConfig($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('option')) return $err;

        $to = (string) $params['to_email'];
        $subject = (string) ($params['subject'] ?? 'AI Connect Admin: email test');
        $body = (string) ($params['body'] ?? "This is a test email sent via XF's configured transport.\n\nIf you receive it, email is working.");

        try {
            // XF 2.3: Mail has no setSubject(). setContent($subject, $html, $text)
            // takes the subject as first arg (was split into setSubject + setBody
            // in older versions).
            /** @var \XF\Mail\Mail $mail */
            $mail = \XF::app()->mailer()->newMail();
            $mail->setTo($to);
            $mail->setContent($subject, $body, $body);
            $mail->send();
            return $this->success([
                'to' => $to,
                'sent' => true,
                'transport' => \XF::options()->emailTransport ?? 'default',
            ]);
        } catch (\Throwable $e) {
            return $this->error('send_failed', 'Test email failed: ' . $e->getMessage());
        }
    }

    public function execute_getAddonOptions($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('option')) return $err;

        $addonId = (string) $params['addon_id'];
        $rows = \XF::db()->fetchAll(
            'SELECT option_id, data_type, default_value FROM xf_option WHERE addon_id = ? ORDER BY option_id',
            [$addonId]
        );
        $opts = \XF::options();
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'option_id' => $r['option_id'],
                'data_type' => $r['data_type'],
                'value'     => property_exists($opts, $r['option_id']) ? $opts->{$r['option_id']} : $r['default_value'],
            ];
        }
        return $this->success(['addon_id' => $addonId, 'count' => count($out), 'options' => $out]);
    }

    public function execute_setAddonOption($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('option')) return $err;

        $addonId  = (string) $params['addon_id'];
        $optionId = (string) $params['option_id'];

        $row = \XF::db()->fetchRow(
            'SELECT addon_id FROM xf_option WHERE option_id = ?',
            [$optionId]
        );
        if (!$row) {
            return $this->error('not_found', "Option '$optionId' not found");
        }
        if ($row['addon_id'] !== $addonId) {
            return $this->error(
                'validation_failed',
                "Option '$optionId' belongs to '{$row['addon_id']}', not '$addonId'. "
                . 'setAddonOption refuses cross-addon writes to prevent accidents.'
            );
        }
        if (in_array($optionId, self::OPTION_BLOCKLIST, true)) {
            return $this->error('no_permission', "Option '$optionId' is on the blocklist");
        }

        // XF 2.3: XF::app()->options()->update() doesn't exist. Use Option entity.
        $opt = \XF::em()->find('XF:Option', $optionId);
        if (!$opt) {
            return $this->error('not_found', "Option entity for '$optionId' not found");
        }
        $opt->option_value = $params['value'];
        if (!$opt->save()) {
            return $this->error('validation_failed', implode(' ', $opt->getErrors()));
        }
        return $this->success([
            'addon_id' => $addonId,
            'option_id' => $optionId,
            'value' => $params['value'],
        ]);
    }
}
