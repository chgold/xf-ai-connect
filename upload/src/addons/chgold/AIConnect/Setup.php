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
            ]
        ];

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
        $this->rebuildAddOnData();
    }

    public function postUpgrade($previousVersion, array &$stateChanges)
    {
        $this->rebuildAddOnData();
    }

    protected function rebuildAddOnData()
    {
        // Simply rebuild the caches - XenForo should have already imported XML data
        // during the install/upgrade process
        \XF::runOnce('aiconnect_rebuild', function () {
            // Rebuild code event listeners cache
            $listenerRepo = \XF::repository('XF:CodeEventListener');
            $listenerRepo->rebuildListenerCache();

            // Rebuild routes cache
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

        // Rebuild caches after deletion
        \XF::repository('XF:CodeEventListener')->rebuildListenerCache();
        \XF::repository('XF:Route')->rebuildRouteCache('public');
        \XF::repository('XF:Route')->rebuildRouteCache('admin');
        \XF::repository('XF:Route')->rebuildRouteCache('api');
    }
}
