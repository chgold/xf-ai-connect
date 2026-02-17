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
        $this->installComposerDependencies();

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

        // Settings table
        $schemaManager->createTable('xf_ai_connect_settings', function(Create $table) {
            $table->addColumn('setting_key', 'varchar', 50);
            $table->addColumn('setting_value', 'text');
            $table->addPrimaryKey('setting_key');
        });
    }

    public function installStep2()
    {
        $db = \XF::db();
        
        $defaults = [
            'enabled' => '1',
            'jwt_secret' => bin2hex(random_bytes(32)),
            'rate_limit_per_minute' => '50',
            'rate_limit_per_hour' => '1000',
            'token_expiry' => '3600',
        ];

        foreach ($defaults as $key => $value) {
            $db->insert('xf_ai_connect_settings', [
                'setting_key' => $key,
                'setting_value' => $value
            ], false, 'setting_value = VALUES(setting_value)');
        }
    }

    protected function installComposerDependencies()
    {
        $addonDir = __DIR__;
        $vendorAutoload = $addonDir . '/vendor/autoload.php';

        if (file_exists($vendorAutoload)) {
            return;
        }

        $composerJson = $addonDir . '/composer.json';
        if (!file_exists($composerJson)) {
            return;
        }

        $composerCmd = trim(shell_exec('which composer 2>/dev/null') ?: '');
        if (!$composerCmd) {
            $composerCmd = trim(shell_exec('which composer.phar 2>/dev/null') ?: '');
        }

        if (!$composerCmd) {
            return;
        }

        $oldDir = getcwd();
        chdir($addonDir);
        shell_exec($composerCmd . ' install --no-dev --optimize-autoloader 2>&1');
        chdir($oldDir);
    }

    public function uninstallStep1()
    {
        $schemaManager = $this->schemaManager();
        
        $schemaManager->dropTable('xf_ai_connect_api_keys');
        $schemaManager->dropTable('xf_ai_connect_rate_limits');
        $schemaManager->dropTable('xf_ai_connect_blocked_users');
        $schemaManager->dropTable('xf_ai_connect_settings');
    }
}
