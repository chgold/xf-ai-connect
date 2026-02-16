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
        $schemaManager->createTable('xf_ai_connect_api_keys', function(Create $table) {
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
        $schemaManager->createTable('xf_ai_connect_rate_limits', function(Create $table) {
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
        $schemaManager->createTable('xf_ai_connect_blocked_users', function(Create $table) {
            $table->addColumn('user_id', 'int');
            $table->addColumn('blocked_date', 'int');
            $table->addColumn('blocked_by_user_id', 'int');
            $table->addColumn('reason', 'text')->nullable();
            $table->addPrimaryKey('user_id');
        });
    }

    /**
     * Create default options
     */
    public function installStep2()
    {
        $options = [
            'aiConnectEnabled' => 1,
            'aiConnectJwtSecret' => bin2hex(random_bytes(32)),
            'aiConnectRateLimitPerMinute' => 50,
            'aiConnectRateLimitPerHour' => 1000,
            'aiConnectTokenExpiry' => 3600, // 1 hour
        ];

        foreach ($options as $key => $value) {
            \XF::db()->insert('xf_option', [
                'option_id' => $key,
                'option_value' => serialize($value),
                'edit_format' => 'textbox',
                'data_type' => is_int($value) ? 'integer' : 'string',
            ], false, 'option_value = VALUES(option_value)');
        }
    }

    /**
     * Drop tables on uninstall
     */
    public function uninstallStep1()
    {
        $schemaManager = $this->schemaManager();
        
        $schemaManager->dropTable('xf_ai_connect_api_keys');
        $schemaManager->dropTable('xf_ai_connect_rate_limits');
        $schemaManager->dropTable('xf_ai_connect_blocked_users');
    }

    /**
     * Remove options on uninstall
     */
    public function uninstallStep2()
    {
        \XF::db()->delete('xf_option', "option_id LIKE 'aiConnect%'");
    }
}
