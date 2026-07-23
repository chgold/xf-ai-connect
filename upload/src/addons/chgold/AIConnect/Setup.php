<?php

namespace chgold\AIConnect;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    use StepRunnerInstallTrait;
    use StepRunnerUninstallTrait;
    use StepRunnerUpgradeTrait;

    /**
     * Create database tables
     */
    public function installStep1()
    {
        $schemaManager = $this->schemaManager();

        // API Keys table
        $schemaManager->createTable('xf_ai_connect_api_keys', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('api_key_id', 'int')->autoIncrement();
            $table->addColumn('user_id', 'int');
            $table->addColumn('api_key', 'varchar', 64);
            $table->addColumn('name', 'varchar', 100);
            $table->addColumn('scopes', 'mediumblob');
            $table->addColumn('is_active', 'tinyint')->setDefault(1);
            $table->addColumn('last_used_date', 'int')->setDefault(0);
            $table->addColumn('created_date', 'int');
            $table->addColumn('expires_date', 'int')->setDefault(0);
            $table->addPrimaryKey('api_key_id');
            $table->addKey('user_id');
            $table->addUniqueKey('api_key');
        });

        // Rate Limits table
        $schemaManager->createTable('xf_ai_connect_rate_limits', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('rate_limit_id', 'int')->autoIncrement();
            $table->addColumn('identifier', 'varchar', 100);
            $table->addColumn('window_type', 'varchar', 20); // minute, hour
            $table->addColumn('window_start', 'int');
            $table->addColumn('request_count', 'int')->setDefault(0);
            $table->addColumn('last_request_date', 'int');
            $table->addPrimaryKey('rate_limit_id');
            $table->addUniqueKey(['identifier', 'window_type', 'window_start']);
        });

        // Blocked Users table
        $schemaManager->createTable('xf_ai_connect_blocked_users', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('user_id', 'int');
            $table->addColumn('blocked_date', 'int');
            $table->addColumn('blocked_by_user_id', 'int');
            $table->addColumn('reason', 'text')->nullable();
            $table->addPrimaryKey('user_id');
        });

        // Settings table
        $schemaManager->createTable('xf_ai_connect_settings', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('setting_key', 'varchar', 50);
            $table->addColumn('setting_value', 'text');
            $table->addPrimaryKey('setting_key');
        });

        // OAuth Clients table
        $schemaManager->createTable('xf_ai_connect_oauth_clients', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('client_id', 'varchar', 80);
            $table->addColumn('client_name', 'varchar', 255);
            $table->addColumn('client_type', 'varchar', 20)->setDefault('public');
            $table->addColumn('redirect_uris', 'text')->nullable();
            $table->addColumn('allowed_scopes', 'text')->nullable();
            $table->addColumn('created_date', 'int');
            $table->addColumn('updated_date', 'int');
            $table->addPrimaryKey('client_id');
        });

        // OAuth Authorization Codes table
        $schemaManager->createTable('xf_ai_connect_oauth_codes', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('code_id', 'int')->autoIncrement();
            $table->addColumn('code', 'varchar', 128);
            $table->addColumn('client_id', 'varchar', 80);
            $table->addColumn('user_id', 'int');
            $table->addColumn('redirect_uri', 'varchar', 500)->nullable();
            $table->addColumn('code_challenge', 'varchar', 128)->nullable();
            $table->addColumn('code_challenge_method', 'varchar', 10)->nullable();
            $table->addColumn('scopes', 'text')->nullable();
            $table->addColumn('state', 'varchar', 128)->nullable();
            $table->addColumn('expires_date', 'int');
            $table->addColumn('used_date', 'int')->setDefault(0);
            $table->addColumn('created_date', 'int');
            $table->addPrimaryKey('code_id');
            $table->addUniqueKey('code');
            $table->addKey(['client_id', 'user_id']);
            $table->addKey('expires_date');
            $table->addKey('state');
        });

        // OAuth Auth Sessions table (session-based auth flow)
        $schemaManager->createTable('xf_ai_connect_auth_sessions', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('session_id', 'varchar', 64);
            $table->addColumn('client_id', 'varchar', 80);
            $table->addColumn('code_verifier', 'varchar', 128);
            $table->addColumn('code_challenge', 'varchar', 128);
            $table->addColumn('expires_date', 'int');
            $table->addColumn('created_date', 'int');
            $table->addPrimaryKey('session_id');
            $table->addKey('expires_date');
        });

        // OAuth Access Tokens table
        $schemaManager->createTable('xf_ai_connect_oauth_tokens', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('token_id', 'int')->autoIncrement();
            $table->addColumn('access_token', 'varchar', 255);
            $table->addColumn('refresh_token', 'varchar', 255)->nullable();
            $table->addColumn('client_id', 'varchar', 80);
            $table->addColumn('user_id', 'int');
            $table->addColumn('scopes', 'text')->nullable();
            $table->addColumn('expires_date', 'int');
            $table->addColumn('refresh_token_expires_date', 'int')->setDefault(0);
            $table->addColumn('revoked_date', 'int')->setDefault(0);
            $table->addColumn('created_date', 'int');
            $table->addPrimaryKey('token_id');
            $table->addUniqueKey('access_token');
            $table->addUniqueKey('refresh_token');
            $table->addKey(['client_id', 'user_id']);
            $table->addKey('expires_date');
        });

        $this->createTokenRegistryTable();
    }

    /**
     * Token Registry — audit trail for every issued token (prefix only).
     * Shared between install (installStep1) and upgrade (upgrade1023100Step1)
     * so the schema definition lives in one place.
     */
    protected function createTokenRegistryTable()
    {
        $this->schemaManager()->createTable('xf_chgold_aiconnect_token_registry', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('id', 'int')->autoIncrement();
            $table->addColumn('token_prefix', 'varchar', 16);
            $table->addColumn('user_id', 'int');
            $table->addColumn('client_id', 'varchar', 80);
            $table->addColumn('scope', 'varchar', 255);
            $table->addColumn('issued_at', 'int');
            $table->addColumn('expires_at', 'int');
            $table->addColumn('refresh_expires_at', 'int')->nullable();
            $table->addColumn('last_used_at', 'int')->nullable();
            $table->addColumn('last_used_ip', 'varchar', 45)->nullable();
            $table->addColumn('last_used_ua', 'varchar', 255)->nullable();
            $table->addColumn('revoked_at', 'int')->nullable();
            $table->addColumn('revoked_by', 'int')->nullable();
            $table->addColumn('source', 'enum')->values(['generator', 'oauth', 'refresh'])->setDefault('oauth');
            $table->addColumn('ip_address', 'varchar', 45)->nullable();
            $table->addPrimaryKey('id');
            $table->addKey('token_prefix');
            $table->addKey('user_id');
            $table->addKey('revoked_at');
        });
    }

    public function installStep2()
    {
        $db = \XF::db();

        $defaults = [
            'enabled' => '1',
            'rate_limit_per_minute' => '50',
            'rate_limit_per_hour' => '1000',
        ];

        foreach ($defaults as $key => $value) {
            $db->insert('xf_ai_connect_settings', [
                'setting_key' => $key,
                'setting_value' => $value
            ], false, 'setting_value = VALUES(setting_value)');
        }

        // Insert default OAuth clients
        $this->insertDefaultOAuthClients();
    }

    protected function insertDefaultOAuthClients()
    {
        $db = \XF::db();
        $time = \XF::$time;

        $clients = [
            // Primary registrations
            [
                'client_id' => 'claude-ai',
                'client_name' => 'Claude AI (Anthropic)',
                'client_type' => 'public',
                'redirect_uris' => json_encode(['urn:ietf:wg:oauth:2.0:oob']),
                'allowed_scopes' => json_encode(['read', 'write']),
                'created_date' => $time,
                'updated_date' => $time
            ],
            [
                'client_id' => 'chatgpt',
                'client_name' => 'ChatGPT (OpenAI)',
                'client_type' => 'public',
                'redirect_uris' => json_encode(['urn:ietf:wg:oauth:2.0:oob']),
                'allowed_scopes' => json_encode(['read', 'write']),
                'created_date' => $time,
                'updated_date' => $time
            ],
            [
                'client_id' => 'gemini',
                'client_name' => 'Gemini (Google)',
                'client_type' => 'public',
                'redirect_uris' => json_encode(['urn:ietf:wg:oauth:2.0:oob']),
                'allowed_scopes' => json_encode(['read', 'write']),
                'created_date' => $time,
                'updated_date' => $time
            ],
            [
                'client_id' => 'grok',
                'client_name' => 'Grok (xAI)',
                'client_type' => 'public',
                'redirect_uris' => json_encode(['urn:ietf:wg:oauth:2.0:oob']),
                'allowed_scopes' => json_encode(['read', 'write']),
                'created_date' => $time,
                'updated_date' => $time
            ],
            [
                'client_id' => 'perplexity',
                'client_name' => 'Perplexity AI',
                'client_type' => 'public',
                'redirect_uris' => json_encode(['urn:ietf:wg:oauth:2.0:oob']),
                'allowed_scopes' => json_encode(['read', 'write']),
                'created_date' => $time,
                'updated_date' => $time
            ],
            [
                'client_id' => 'copilot',
                'client_name' => 'Microsoft Copilot',
                'client_type' => 'public',
                'redirect_uris' => json_encode(['urn:ietf:wg:oauth:2.0:oob']),
                'allowed_scopes' => json_encode(['read', 'write']),
                'created_date' => $time,
                'updated_date' => $time
            ],
            [
                'client_id' => 'meta-ai',
                'client_name' => 'Meta AI (Facebook)',
                'client_type' => 'public',
                'redirect_uris' => json_encode(['urn:ietf:wg:oauth:2.0:oob']),
                'allowed_scopes' => json_encode(['read', 'write']),
                'created_date' => $time,
                'updated_date' => $time
            ],
            [
                'client_id' => 'deepseek',
                'client_name' => 'DeepSeek AI',
                'client_type' => 'public',
                'redirect_uris' => json_encode(['urn:ietf:wg:oauth:2.0:oob']),
                'allowed_scopes' => json_encode(['read', 'write']),
                'created_date' => $time,
                'updated_date' => $time
            ],
            // Common AI agent client_id variants (aliases)
        ];

        $oob = json_encode(['urn:ietf:wg:oauth:2.0:oob']);
        $rw  = json_encode(['read', 'write']);
        $aliases = [
            ['gemini_client', 'Gemini (Google)'],
            ['claude',        'Claude AI (Anthropic)'],
            ['claude_client', 'Claude AI (Anthropic)'],
            ['chatgpt_client','ChatGPT (OpenAI)'],
            ['openai',        'ChatGPT (OpenAI)'],
            ['google',        'Gemini (Google)'],
        ];
        foreach ($aliases as [$id, $name]) {
            $clients[] = [
                'client_id'      => $id,
                'client_name'    => $name,
                'client_type'    => 'public',
                'redirect_uris'  => $oob,
                'allowed_scopes' => $rw,
                'created_date'   => $time,
                'updated_date'   => $time,
            ];
        }
        $clients = array_merge([], $clients); // re-index

        foreach ($clients as $client) {
            $exists = $db->fetchOne(
                'SELECT client_id FROM xf_ai_connect_oauth_clients WHERE client_id = ?',
                $client['client_id']
            );

            if (!$exists) {
                $db->insert('xf_ai_connect_oauth_clients', $client);
            }
        }
    }

    public function postInstall(array &$stateChanges)
    {
        $this->setupNavigation();
        $this->setupDefaultPermissions();
        $this->syncToolPermissions();
        $this->rebuildAddOnData();
    }

    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        $this->applyMissingSchemaFixes();
        $this->setupNavigation();
        $this->setupDefaultPermissions();
        $this->syncToolPermissions();
        $this->rebuildAddOnData();
    }

    /**
     * Idempotent schema fixes — safe to run on every upgrade.
     * Ensures columns/tables added in later versions exist even if
     * the version-specific upgrade step was skipped (e.g. jumped versions).
     */
    protected function applyMissingSchemaFixes()
    {
        $schemaManager = $this->schemaManager();

        // state column — added in v1.2.27
        $schemaManager->alterTable('xf_ai_connect_oauth_codes', function (Alter $table) {
            if (!$table->getColumnDefinition('state')) {
                $table->addColumn('state', 'varchar', 128)->nullable()->after('scopes');
                $table->addKey('state');
            }
        });

        // auth_sessions table — added in v1.2.27
        $schemaManager->createTable('xf_ai_connect_auth_sessions', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('session_id', 'varchar', 64);
            $table->addColumn('client_id', 'varchar', 80);
            $table->addColumn('code_verifier', 'varchar', 128);
            $table->addColumn('code_challenge', 'varchar', 128);
            $table->addColumn('expires_date', 'int');
            $table->addColumn('created_date', 'int');
            $table->addPrimaryKey('session_id');
            $table->addKey('expires_date');
        });

        // token_registry table — added in v1.2.31
        $this->createTokenRegistryTable();
    }

    /**
     * Creates the public navigation entry for AI Connect.
     * XenForo does not export/import public navigation via addon data files,
     * so we create it programmatically here.
     */
    protected function setupNavigation()
    {
        $db = \XF::db();

        $dataExpr = "[\n\t\t'title' => 'AI Connect',\n\t\t'href' => \$__templater->func('link', array('ai-connect', ), false),\n\t\t'attributes' => [],\n\t]";
        // Guests and visitors without tool access see the link when aiconnect_nav_top is enabled.
        // Logged-in users also need useTools permission.
        $condExpr  = "\n\t\$__vars['xf']['options']['aiconnect_nav_top'] && \$__vars['xf']['visitor']->hasPermission('aiconnect', 'viewAiConnect') && (!\$__vars['xf']['visitor']->user_id || \$__vars['xf']['visitor']->hasPermission('aiconnect', 'useTools'))";

        $existing = $db->fetchOne('SELECT navigation_id FROM xf_navigation WHERE navigation_id = ?', ['ai_connect']);

        if ($existing) {
            $db->update('xf_navigation', [
                'display_order'      => 700,
                'navigation_type_id' => 'basic',
                'enabled'            => 1,
                'data_expression'    => $dataExpr,
                'condition_expression' => $condExpr,
            ], 'navigation_id = ?', ['ai_connect']);
        } else {
            $db->insert('xf_navigation', [
                'navigation_id'        => 'ai_connect',
                'parent_navigation_id' => '',
                'display_order'        => 700,
                'navigation_type_id'   => 'basic',
                'type_config'          => '',
                'condition_expression' => $condExpr,
                'condition_setup'      => '',
                'data_expression'      => $dataExpr,
                'data_setup'           => '',
                'global_setup'         => '',
                'enabled'              => 1,
                'is_customized'        => 0,
                'default_value'        => '',
                'addon_id'             => 'chgold/AIConnect',
            ]);
        }

        \XF::repository('XF:Navigation')->rebuildNavigationCache();
    }

    /**
     * Sets default permissions on install/upgrade.
     * Preserves explicit admin choices (allow/deny) but restores missing or
     * 'unset' entries to their defaults — so critical permissions are never
     * silently absent after an upgrade or accidental deletion.
     *
     * Defaults:
     *   viewAiConnect — Allow: Guests(1), Registered(2)
     *   useTools      — Allow: Registered(2) only  (guests cannot authenticate)
     */
    protected function setupDefaultPermissions()
    {
        $db      = \XF::db();
        $rebuild = false;

        $defaults = [
            ['viewAiConnect', 1, 'allow'],
            ['viewAiConnect', 2, 'allow'],
            ['useTools',      2, 'allow'],
        ];

        foreach ($defaults as [$permId, $groupId, $value]) {
            $existing = $db->fetchOne(
                'SELECT permission_value FROM xf_permission_entry
                 WHERE user_group_id = ? AND user_id = 0
                   AND permission_group_id = ? AND permission_id = ?',
                [$groupId, 'aiconnect', $permId]
            );

            if ($existing === false || $existing === null) {
                $db->insert('xf_permission_entry', [
                    'user_group_id'        => $groupId,
                    'user_id'              => 0,
                    'permission_group_id'  => 'aiconnect',
                    'permission_id'        => $permId,
                    'permission_value'     => $value,
                    'permission_value_int' => 0,
                ]);
                $rebuild = true;
            } elseif ($existing === 'unset') {
                $db->update(
                    'xf_permission_entry',
                    ['permission_value' => $value],
                    'user_group_id = ? AND user_id = 0
                     AND permission_group_id = ? AND permission_id = ?',
                    [$groupId, 'aiconnect', $permId]
                );
                $rebuild = true;
            }
        }

        if ($rebuild) {
            \XF::app()->jobManager()->enqueueUnique(
                'aiconnect_perm_rebuild',
                'XF:PermissionRebuild',
                [],
                false
            );
        }
    }

    /**
     * Dynamically registers per-tool permissions in xf_permission for every
     * tool defined in all modules. Called on install and upgrade so new tools
     * added in future versions are automatically registered.
     *
     * Permission ID format: tool_{moduleName}_{toolName}
     * Interface group:      aiconnect_tools
     * Depends on:           aiconnect.useTools  (master switch)
     * Default:              Allow for Registered users (group 2)
     *
     * Third-party modules can hook into the 'ai_connect_sync_tool_permissions'
     * code event to register their own tool permissions.
     */
    protected function syncToolPermissions()
    {
        $db = \XF::db();

        // Static tool labels: [moduleName => [toolName => humanLabel]]
        // Maintained here (not by instantiating modules) to avoid side effects
        // during setup and to support offline / headless installs.
        $toolDefs = [
            'xenforo' => [
                'searchThreads'  => 'Tool: Search threads',
                'getThread'      => 'Tool: Get thread by ID',
                'searchPosts'    => 'Tool: Search posts',
                'getPost'        => 'Tool: Get post by ID',
                'getCurrentUser' => 'Tool: Get current user info',
            ],
            'translation' => [
                'translate'             => 'Tool: Translate text',
                'getSupportedLanguages' => 'Tool: Get supported languages',
            ],
        ];

        // Allow third-party addons to register additional tool permissions and packages.
        // $packageDefs format: [ 'packageId' => ['label' => 'Display Name', 'display_order' => 320,
        //   'modules' => ['module_name' => ['toolName' => 'Tool Label', ...]] ] ]
        $packageDefs = [];
        \XF::fire('ai_connect_sync_tool_permissions', [&$toolDefs, &$packageDefs], 'chgold/AIConnect');

        // Register package permissions if any were provided
        if (!empty($packageDefs)) {
            $this->syncPackagePermissions($db, $packageDefs);
        }

        // Ensure both interface groups exist in DB.
        // They are defined in permission_interface_groups.xml, but build-release
        // may overwrite that file with stale DB content on the dev machine.
        // Inserting here guarantees they exist on any install or upgrade.
        $interfaceGroups = [
            ['aiconnect_general', 300, 'AI Connect'],
            ['aiconnect_tools',   310, 'AI Connect — Tools'],
        ];
        foreach ($interfaceGroups as [$groupId, $order, $label]) {
            $igExists = $db->fetchOne(
                'SELECT interface_group_id FROM xf_permission_interface_group WHERE interface_group_id = ?',
                [$groupId]
            );
            if (!$igExists) {
                $db->insert('xf_permission_interface_group', [
                    'interface_group_id' => $groupId,
                    'display_order'      => $order,
                    'is_moderator'       => 0,
                    'addon_id'           => 'chgold/AIConnect',
                ]);
                self::compilePhrase('permission_interface.' . $groupId, $label);
            }
        }

        // Ensure static permission phrases are compiled (viewAiConnect, useTools).
        $staticPhrases = [
            'permission.aiconnect_viewAiConnect' => 'View AI Connect (navigation links and info page)',
            'permission.aiconnect_useTools'      => 'Use AI Connect tools (master switch for all tools)',
        ];
        foreach ($staticPhrases as $phraseKey => $phraseText) {
            self::compilePhrase($phraseKey, $phraseText);
        }

        // Migrate: fix display_order for interface groups (v1.2.13+) — place after XF built-in groups.
        $db->query(
            'UPDATE xf_permission_interface_group SET display_order = ? WHERE interface_group_id = ? AND display_order != ?',
            [300, 'aiconnect_general', 300]
        );
        $db->query(
            'UPDATE xf_permission_interface_group SET display_order = ? WHERE interface_group_id = ? AND display_order != ?',
            [310, 'aiconnect_tools', 310]
        );

        // Migrate: move useTools into aiconnect_tools interface group (v1.2.13+).
        // In earlier versions it was in aiconnect_general; moving it places it at the
        // top of the Tools section so XF visually greys out dependent per-tool
        // permissions when the master switch is denied.
        $db->query(
            'UPDATE xf_permission
             SET interface_group_id = ?, display_order = ?
             WHERE permission_group_id = ? AND permission_id = ? AND interface_group_id != ?',
            ['aiconnect_tools', 5, 'aiconnect', 'useTools', 'aiconnect_tools']
        );

        $rebuild = false;

        foreach ($toolDefs as $moduleName => $tools) {
            foreach ($tools as $toolName => $label) {
                $permId    = \chgold\AIConnect\Helper\Permission::toolPermId($moduleName, $toolName);
                $phraseKey = 'permission.aiconnect_' . $permId;

                // 1. Register the permission in xf_permission (once)
                $permExists = $db->fetchOne(
                    'SELECT permission_id FROM xf_permission
                     WHERE permission_group_id = ? AND permission_id = ?',
                    ['aiconnect', $permId]
                );

                if (!$permExists) {
                    $db->insert('xf_permission', [
                        'permission_id'        => $permId,
                        'permission_group_id'  => 'aiconnect',
                        'permission_type'      => 'flag',
                        'interface_group_id'   => 'aiconnect_tools',
                        'depend_permission_id' => 'useTools',
                        'display_order'        => 10,
                        'addon_id'             => 'chgold/AIConnect',
                    ]);
                }

                // 2. Register the phrase for Admin CP display
                $phraseExists = $db->fetchOne(
                    'SELECT title FROM xf_phrase WHERE language_id = 0 AND title = ?',
                    [$phraseKey]
                );

                if (!$phraseExists) {
                    $db->insert('xf_phrase', [
                        'language_id'    => 0,
                        'title'          => $phraseKey,
                        'phrase_text'    => $label,
                        'addon_id'       => 'chgold/AIConnect',
                        'version_id'     => 1021100,
                        'version_string' => '1.2.11',
                        'global_cache'   => 0,
                    ]);
                    self::compilePhrase($phraseKey, $label);
                }

                // 3. Default Allow for Registered users (group 2) — only if not yet set
                $entryExists = $db->fetchOne(
                    'SELECT permission_value FROM xf_permission_entry
                     WHERE user_group_id = 2 AND user_id = 0
                       AND permission_group_id = ? AND permission_id = ?',
                    ['aiconnect', $permId]
                );

                if ($entryExists === false || $entryExists === null) {
                    $db->insert('xf_permission_entry', [
                        'user_group_id'        => 2,
                        'user_id'              => 0,
                        'permission_group_id'  => 'aiconnect',
                        'permission_id'        => $permId,
                        'permission_value'     => 'allow',
                        'permission_value_int' => 0,
                    ]);
                    $rebuild = true;
                }
            }
        }

        if ($rebuild) {
            \XF::app()->jobManager()->enqueueUnique(
                'aiconnect_perm_rebuild',
                'XF:PermissionRebuild',
                [],
                false
            );
        }
    }

    /**
     * Registers package-level permissions and their per-tool permissions.
     * Called by syncToolPermissions() when Pro addons register packages via code event.
     *
     * Package permission ID:  use_package_{packageId}   (depends on useTools)
     * Per-tool permission ID: tool_{packageId}_{toolName} truncated to 25 chars
     *
     * @param \XF\Db\AbstractAdapter $db
     * @param array $packageDefs  [packageId => ['label'=>..., 'display_order'=>..., 'modules'=>[...]]]
     */
    public static function syncPackagePermissions(
        \XF\Db\AbstractAdapter $db,
        array $packageDefs,
        string $addonId = 'chgold/AIConnect'
    ) {
        $rebuild = false;

        foreach ($packageDefs as $packageId => $packageConfig) {
            $label        = $packageConfig['label'] ?? 'AI Connect — ' . ucfirst($packageId) . ' Tools';
            $displayOrder = $packageConfig['display_order'] ?? 400;
            $igId         = 'aiconnect_pkg_' . $packageId;

            // Ensure interface group exists
            $igExists = $db->fetchOne(
                'SELECT interface_group_id FROM xf_permission_interface_group WHERE interface_group_id = ?',
                [$igId]
            );
            if (!$igExists) {
                $db->insert('xf_permission_interface_group', [
                    'interface_group_id' => $igId,
                    'display_order'      => $displayOrder,
                    'is_moderator'       => 0,
                    'addon_id'           => $addonId,
                ]);
            }
            // Always compile phrase — idempotent, ensures Admin CP shows correct label
            self::compilePhrase('permission_interface.' . $igId, $label);

            // Ensure package master permission: use_package_{packageId}
            $rawPkgPerm = 'use_package_' . $packageId;
            $pkgPermId  = strlen($rawPkgPerm) <= 25 ? $rawPkgPerm : substr($rawPkgPerm, 0, 25);

            $pkgPermExists = $db->fetchOne(
                'SELECT permission_id FROM xf_permission WHERE permission_group_id = ? AND permission_id = ?',
                ['aiconnect', $pkgPermId]
            );
            if (!$pkgPermExists) {
                $db->insert('xf_permission', [
                    'permission_id'        => $pkgPermId,
                    'permission_group_id'  => 'aiconnect',
                    'permission_type'      => 'flag',
                    'interface_group_id'   => $igId,
                    'depend_permission_id' => 'useTools',
                    'display_order'        => 5,
                    'addon_id'             => $addonId,
                ]);
            }
            // Always compile phrase — idempotent
            self::compilePhrase('permission.aiconnect_' . $pkgPermId, 'Use ' . $label . ' (package switch)');

            // Default Allow for Registered users (group 2) — only if not yet set
            $pkgEntryExists = $db->fetchOne(
                'SELECT permission_value FROM xf_permission_entry
                 WHERE user_group_id = 2 AND user_id = 0
                   AND permission_group_id = ? AND permission_id = ?',
                ['aiconnect', $pkgPermId]
            );
            if ($pkgEntryExists === false || $pkgEntryExists === null) {
                $db->insert('xf_permission_entry', [
                    'user_group_id'        => 2,
                    'user_id'              => 0,
                    'permission_group_id'  => 'aiconnect',
                    'permission_id'        => $pkgPermId,
                    'permission_value'     => 'allow',
                    'permission_value_int' => 0,
                ]);
                $rebuild = true;
            }

            // Register per-tool permissions for this package
            $modules = $packageConfig['modules'] ?? [];
            foreach ($modules as $moduleName => $tools) {
                foreach ($tools as $toolName => $toolLabel) {
                    $permId    = \chgold\AIConnect\Helper\Permission::toolPermId($moduleName, $toolName);
                    $phraseKey = 'permission.aiconnect_' . $permId;

                    $permExists = $db->fetchOne(
                        'SELECT permission_id FROM xf_permission WHERE permission_group_id = ? AND permission_id = ?',
                        ['aiconnect', $permId]
                    );
                    if (!$permExists) {
                        $db->insert('xf_permission', [
                            'permission_id'        => $permId,
                            'permission_group_id'  => 'aiconnect',
                            'permission_type'      => 'flag',
                            'interface_group_id'   => $igId,
                            'depend_permission_id' => $pkgPermId,
                            'display_order'        => 10,
                            'addon_id'             => $addonId,
                        ]);
                        $rebuild = true;
                    }
                    // Always compile phrase — idempotent, keeps Admin CP labels fresh
                    $phraseExists = $db->fetchOne(
                        'SELECT title FROM xf_phrase WHERE language_id = 0 AND title = ?',
                        [$phraseKey]
                    );
                    if (!$phraseExists) {
                        $db->insert('xf_phrase', [
                            'language_id'    => 0,
                            'title'          => $phraseKey,
                            'phrase_text'    => $toolLabel,
                            'addon_id'       => $addonId,
                            'version_id'     => 1021500,
                            'version_string' => '1.2.15',
                            'global_cache'   => 0,
                        ]);
                    }
                    self::compilePhrase($phraseKey, $toolLabel);

                    // Default Allow for Registered (group 2)
                    $entryExists = $db->fetchOne(
                        'SELECT permission_value FROM xf_permission_entry
                         WHERE user_group_id = 2 AND user_id = 0
                           AND permission_group_id = ? AND permission_id = ?',
                        ['aiconnect', $permId]
                    );
                    if ($entryExists === false || $entryExists === null) {
                        $db->insert('xf_permission_entry', [
                            'user_group_id'        => 2,
                            'user_id'              => 0,
                            'permission_group_id'  => 'aiconnect',
                            'permission_id'        => $permId,
                            'permission_value'     => 'allow',
                            'permission_value_int' => 0,
                        ]);
                        $rebuild = true;
                    }
                }
            }
        }

        if ($rebuild) {
            \XF::app()->jobManager()->enqueueUnique(
                'aiconnect_perm_rebuild',
                'XF:PermissionRebuild',
                [],
                false
            );
        }
    }

    /**
     * Inserts or updates a phrase in xf_phrase_compiled for all active languages.
     * Used when dynamically registering per-tool permissions during setup.
     *
     * @param string $title      Phrase key (e.g. 'permission.aiconnect_tool_x_y')
     * @param string $phraseText Human-readable text
     */
    public static function compilePhrase(string $title, string $phraseText)
    {
        $db        = \XF::db();
        $languages = $db->fetchAllColumn('SELECT language_id FROM xf_language');
        // Always include language_id=0 (master)
        $langIds   = array_unique(array_merge([0], $languages));

        foreach ($langIds as $langId) {
            $db->insert(
                'xf_phrase_compiled',
                [
                    'language_id' => $langId,
                    'title'       => $title,
                    'phrase_text' => $phraseText,
                ],
                false,
                'phrase_text = VALUES(phrase_text)'
            );
        }
    }

    protected function rebuildAddOnData()
    {
        \XF::runOnce('aiconnect_rebuild', function () {
            $listenerRepo = \XF::repository('XF:CodeEventListener');
            $listenerRepo->rebuildListenerCache();

            $routeRepo = \XF::repository('XF:Route');
            $routeRepo->rebuildRouteCache('public');
            $routeRepo->rebuildRouteCache('admin');
            $routeRepo->rebuildRouteCache('api');
        });
    }

    /**
     * v1.2.27 — add state column to oauth_codes + create auth_sessions table.
     * Runs for any installation upgrading from below v1.2.27 (version_id 1022700).
     */
    public function upgrade1022700Step1()
    {
        $schemaManager = $this->schemaManager();

        $schemaManager->alterTable('xf_ai_connect_oauth_codes', function (Alter $table) {
            if (!$table->getColumnDefinition('state')) {
                $table->addColumn('state', 'varchar', 128)->nullable()->after('scopes');
                $table->addKey('state');
            }
        });

        $schemaManager->createTable('xf_ai_connect_auth_sessions', function (Create $table) {
            $table->checkExists(true);
            $table->addColumn('session_id', 'varchar', 64);
            $table->addColumn('client_id', 'varchar', 80);
            $table->addColumn('code_verifier', 'varchar', 128);
            $table->addColumn('code_challenge', 'varchar', 128);
            $table->addColumn('expires_date', 'int');
            $table->addColumn('created_date', 'int');
            $table->addPrimaryKey('session_id');
            $table->addKey('expires_date');
        });
    }

    /**
     * v1.2.31 — create token_registry table for the audit trail of issued tokens.
     */
    public function upgrade1023100Step1()
    {
        $this->createTokenRegistryTable();
    }

    /**
     * v1.2.34 — add last_used_ip and last_used_ua columns to token_registry.
     */
    public function upgrade1023400Step1()
    {
        $this->schemaManager()->alterTable('xf_chgold_aiconnect_token_registry', function (Alter $table) {
            if (!$table->getColumnDefinition('last_used_ip')) {
                $table->addColumn('last_used_ip', 'varchar', 45)->nullable()->after('last_used_at');
            }
            if (!$table->getColumnDefinition('last_used_ua')) {
                $table->addColumn('last_used_ua', 'varchar', 255)->nullable()->after('last_used_ip');
            }
        });
    }

    /**
     * v1.2.35.2 — auto-revoke legacy pre-1.2.31 tokens.
     *
     * Tokens that were backfilled by 1.2.35.1 from xf_ai_connect_oauth_tokens
     * have no last_used_at / last_used_ip / last_used_ua history (we didn't
     * track that data before the registry existed). There is no reliable way
     * for the user to tell which of these are still being used by AI agents
     * vs forgotten legacy.
     *
     * Secure-by-default: revoke every such token on upgrade and cascade to
     * the underlying oauth_token so the refresh_token is killed too. Users
     * regenerate tokens after upgrade if they still need them.
     *
     * Identification criteria for "legacy backfilled":
     *   - revoked_at IS NULL OR 0   (still active)
     *   - last_used_at IS NULL      (never recorded a use)
     *   - last_used_ip IS NULL      (no IP recorded — registry never saw it used)
     *   - last_used_ua IS NULL      (no UA recorded — same)
     *   - source = 'oauth'          (rules out 'generator' tokens, which UI just issued)
     *
     * A token created in the UI seconds before upgrade matches these too,
     * but that's an acceptable edge case: the user just regenerates.
     *
     * Idempotent: the WHERE filters skip already-revoked rows, so a second
     * run is a no-op.
     */
    public function upgrade1023502Step1()
    {
        try {
            $time = \XF::$time;
            $db   = \XF::db();

            // Step 1: revoke registry rows that look like legacy backfill.
            $db->query(
                "UPDATE xf_chgold_aiconnect_token_registry
                 SET revoked_at = ?, revoked_by = 0
                 WHERE (revoked_at IS NULL OR revoked_at = 0)
                   AND last_used_at IS NULL
                   AND last_used_ip IS NULL
                   AND last_used_ua IS NULL
                   AND source = 'oauth'",
                [$time]
            );

            // Step 2: cascade to oauth_tokens so the refresh_token dies too.
            // Joined criterion guarantees we only touch oauth rows whose
            // registry row was system-revoked above (revoked_by = 0) and
            // remained in the "never used" legacy state.
            $db->query(
                "UPDATE xf_ai_connect_oauth_tokens o
                 INNER JOIN xf_chgold_aiconnect_token_registry r
                         ON SUBSTRING(o.access_token, 1, 16) = r.token_prefix
                 SET o.revoked_date = ?
                 WHERE o.revoked_date = 0
                   AND r.revoked_by = 0
                   AND r.last_used_at IS NULL
                   AND r.last_used_ip IS NULL
                   AND r.last_used_ua IS NULL",
                [$time]
            );
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'AIConnect 1.2.35.2 auto-revoke failed: ');
        }
    }

    /**
     * v1.2.35.1 — backfill registry for tokens issued BEFORE v1.2.31, when
     * the registry table did not exist yet.
     *
     * Without this, those legacy tokens are invisible in the user-facing
     * "My AI Tokens" UI and the "Revoke all" action silently skips them —
     * letting AI agents keep refreshing access tokens after the user
     * explicitly revoked everything. That is a security regression on upgrade.
     *
     * Idempotent: a LEFT JOIN guards against double-insertion, so re-running
     * this step is a no-op for rows already present in the registry.
     */
    public function upgrade1023501Step1()
    {
        try {
            \XF::db()->query("
                INSERT INTO xf_chgold_aiconnect_token_registry
                    (token_prefix, user_id, client_id, scope, issued_at, expires_at,
                     refresh_expires_at, revoked_at, source, ip_address)
                SELECT
                    SUBSTRING(o.access_token, 1, 16),
                    o.user_id,
                    o.client_id,
                    COALESCE(REPLACE(REPLACE(REPLACE(REPLACE(o.scopes, '[', ''), ']', ''), '\"', ''), ',', ' '), ''),
                    o.created_date,
                    o.expires_date,
                    o.refresh_token_expires_date,
                    CASE WHEN o.revoked_date > 0 THEN o.revoked_date ELSE NULL END,
                    'oauth',
                    NULL
                FROM xf_ai_connect_oauth_tokens o
                LEFT JOIN xf_chgold_aiconnect_token_registry r
                  ON r.token_prefix = SUBSTRING(o.access_token, 1, 16)
                WHERE r.id IS NULL
            ");
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'AIConnect 1.2.35.1 backfill failed: ');
        }
    }

    /**
     * v1.2.35 — add refresh_expires_at column to token_registry so the UI
     * can distinguish "renewable" tokens (access expired but refresh still
     * valid) and keep them manageable in the user-facing token list.
     *
     * Also backfills the new column from xf_ai_connect_oauth_tokens for
     * existing rows, matching by the 16-char access-token prefix.
     */
    public function upgrade1023500Step1()
    {
        $this->schemaManager()->alterTable('xf_chgold_aiconnect_token_registry', function (Alter $table) {
            if (!$table->getColumnDefinition('refresh_expires_at')) {
                $table->addColumn('refresh_expires_at', 'int')->nullable()->after('expires_at');
            }
        });

        // Backfill from the live oauth_tokens table (one-time data migration).
        try {
            \XF::db()->query(
                'UPDATE xf_chgold_aiconnect_token_registry r
                 INNER JOIN xf_ai_connect_oauth_tokens o
                         ON LEFT(o.access_token, 16) = r.token_prefix
                 SET r.refresh_expires_at = o.refresh_token_expires_date
                 WHERE r.refresh_expires_at IS NULL'
            );
        } catch (\Throwable $e) {
            \XF::logException($e, false, 'AIConnect 1.2.35 backfill failed: ');
        }
    }

    public function uninstallStep1()
    {
        $schemaManager = $this->schemaManager();
        $db = \XF::db();

        $tables = [
            'xf_ai_connect_api_keys',
            'xf_ai_connect_rate_limits',
            'xf_ai_connect_blocked_users',
            'xf_ai_connect_settings',
            'xf_ai_connect_oauth_tokens',
            'xf_ai_connect_oauth_codes',
            'xf_ai_connect_oauth_clients',
            'xf_ai_connect_auth_sessions',
            'xf_chgold_aiconnect_token_registry',
        ];

        foreach ($tables as $table) {
            // Drop main table
            $schemaManager->dropTable($table);

            // Drop any leftover conflict tables
            $conflicts = $db->fetchAllColumn("SHOW TABLES LIKE '{$table}__conflict%'");
            foreach ($conflicts as $conflictTable) {
                $db->query("DROP TABLE IF EXISTS `{$conflictTable}`");
            }
        }
    }

    public function uninstallStep2()
    {
        // Explicitly delete code event listeners
        $db = \XF::db();
        $db->delete('xf_code_event_listener', 'addon_id = ?', 'chgold/AIConnect');

        // Explicitly delete routes
        $db->delete('xf_route', 'addon_id = ?', 'chgold/AIConnect');

        // Remove navigation entry
        $db->delete('xf_navigation', 'navigation_id = ?', 'ai_connect');
        \XF::repository('XF:Navigation')->rebuildNavigationCache();

        // Rebuild caches after deletion
        \XF::repository('XF:CodeEventListener')->rebuildListenerCache();
        \XF::repository('XF:Route')->rebuildRouteCache('public');
        \XF::repository('XF:Route')->rebuildRouteCache('admin');
        \XF::repository('XF:Route')->rebuildRouteCache('api');
    }
}
