<?php

namespace chgold\AIConnect;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
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
            $table->addColumn('expires_date', 'int');
            $table->addColumn('used_date', 'int')->setDefault(0);
            $table->addColumn('created_date', 'int');
            $table->addPrimaryKey('code_id');
            $table->addUniqueKey('code');
            $table->addKey(['client_id', 'user_id']);
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
        $this->setupNavigation();
        $this->setupDefaultPermissions();
        $this->syncToolPermissions();
        $this->rebuildAddOnData();
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
        $condExpr  = "\n\t\$__vars['xf']['options']['aiconnect_nav_top'] && \$__vars['xf']['visitor']->hasPermission('aiconnect', 'viewAiConnect')";

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
     * Only sets values not yet explicitly configured (preserves admin customizations).
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
            // [permission_id, user_group_id, value]
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
                $this->compilePhrase('permission_interface.' . $groupId, $label);
            }
        }

        // Ensure static permission phrases are compiled (viewAiConnect, useTools).
        $staticPhrases = [
            'permission.aiconnect_viewAiConnect' => 'View AI Connect (navigation links and info page)',
            'permission.aiconnect_useTools'      => 'Use AI Connect tools (master switch for all tools)',
        ];
        foreach ($staticPhrases as $phraseKey => $phraseText) {
            $this->compilePhrase($phraseKey, $phraseText);
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
                // xf_permission.permission_id is varbinary(25) — truncate if needed
                $rawPermId = 'tool_' . $moduleName . '_' . $toolName;
                $permId    = strlen($rawPermId) <= 25 ? $rawPermId : substr($rawPermId, 0, 25);
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
                    $this->compilePhrase($phraseKey, $label);
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
    protected function syncPackagePermissions(\XF\Db\AbstractAdapter $db, array $packageDefs)
    {
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
                    'addon_id'           => 'chgold/AIConnect',
                ]);
                $this->compilePhrase('permission_interface.' . $igId, $label);
            }

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
                    'addon_id'             => 'chgold/AIConnect',
                ]);
                $this->compilePhrase('permission.aiconnect_' . $pkgPermId, 'Use ' . $label . ' (package switch)');
            }

            // Register per-tool permissions for this package
            $modules = $packageConfig['modules'] ?? [];
            foreach ($modules as $moduleName => $tools) {
                foreach ($tools as $toolName => $toolLabel) {
                    $rawPermId = 'tool_' . $moduleName . '_' . $toolName;
                    $permId    = strlen($rawPermId) <= 25 ? $rawPermId : substr($rawPermId, 0, 25);
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
                            'addon_id'             => 'chgold/AIConnect',
                        ]);
                        $phraseExists = $db->fetchOne(
                            'SELECT title FROM xf_phrase WHERE language_id = 0 AND title = ?',
                            [$phraseKey]
                        );
                        if (!$phraseExists) {
                            $db->insert('xf_phrase', [
                                'language_id'    => 0,
                                'title'          => $phraseKey,
                                'phrase_text'    => $toolLabel,
                                'addon_id'       => 'chgold/AIConnect',
                                'version_id'     => 1021500,
                                'version_string' => '1.2.15',
                                'global_cache'   => 0,
                            ]);
                            $this->compilePhrase($phraseKey, $toolLabel);
                        }
                        $rebuild = true;
                    }

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
    protected function compilePhrase(string $title, string $phraseText)
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
