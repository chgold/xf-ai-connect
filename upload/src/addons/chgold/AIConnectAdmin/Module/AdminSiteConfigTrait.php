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
            'description' => 'Set email transport (smtp/default). Password is stored via XF s '
                . 'option encryption. Recommended: call testEmailConfig after.',
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
                    'smtp_encryption' => ['type' => 'string', 'enum' => ['', 'ssl', 'tls']],
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

        // XF 2.3: emailTransport is an ARRAY option containing the transport
        // name AND all SMTP fields nested — e.g.
        //   {"emailTransport": "smtp", "host": "smtp.example.com",
        //    "port": 587, "username": "user", "password": "...",
        //    "encryption": "tls"}
        $opts = \XF::options();
        $emailTransport = $opts->emailTransport;
        $config = is_array($emailTransport) ? $emailTransport : ['emailTransport' => 'sendmail'];
        $transport = (string) ($config['emailTransport'] ?? 'sendmail');

        $pwd = $config['password'] ?? '';
        $maskedPwd = $pwd === '' ? '' : '****' . substr($pwd, -4);

        return $this->success([
            'transport'  => $transport,
            'from_email' => $opts->defaultEmailAddress ?? '',
            'from_name'  => $opts->emailSenderName ?? '',
            'smtp' => [
                'host'       => $config['host'] ?? '',
                'port'       => (int) ($config['port'] ?? 0),
                'encryption' => $config['encryption'] ?? '',
                'username'   => $config['username'] ?? '',
                'password'   => $maskedPwd,
            ],
        ]);
    }

    public function execute_setEmailTransportConfig($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('option')) return $err;

        // emailTransport is an ARRAY option containing transport + all SMTP
        // fields nested together — merge into existing structure.
        $current = \XF::options()->emailTransport;
        if (!is_array($current)) $current = ['emailTransport' => 'sendmail'];

        $current['emailTransport'] = (string) $params['transport'];
        if ($params['transport'] === 'smtp') {
            if (isset($params['smtp_host']))       $current['host']       = (string) $params['smtp_host'];
            if (isset($params['smtp_port']))       $current['port']       = (int)    $params['smtp_port'];
            if (isset($params['smtp_encryption'])) $current['encryption'] = (string) $params['smtp_encryption'];
            if (isset($params['smtp_username']))   $current['username']   = (string) $params['smtp_username'];
            if (isset($params['smtp_password']) && $params['smtp_password'] !== '') {
                $current['password'] = (string) $params['smtp_password'];
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
            'transport' => $params['transport'],
            'note' => 'Config saved. Run testEmailConfig to verify connectivity.',
        ]);
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
