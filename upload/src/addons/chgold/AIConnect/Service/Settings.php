<?php

namespace chgold\AIConnect\Service;

class Settings
{
    protected static $cache = null;

    public static function get($key, $default = null)
    {
        if (self::$cache === null) {
            self::loadSettings();
        }

        return self::$cache[$key] ?? $default;
    }

    public static function set($key, $value)
    {
        \XF::db()->insert('xf_ai_connect_settings', [
            'setting_key' => $key,
            'setting_value' => $value
        ], false, 'setting_value = VALUES(setting_value)');

        if (self::$cache !== null) {
            self::$cache[$key] = $value;
        }
    }

    protected static function loadSettings()
    {
        self::$cache = [];

        $settings = \XF::db()->fetchPairs(
            'SELECT setting_key, setting_value FROM xf_ai_connect_settings'
        );

        self::$cache = $settings ?: [];

        self::applyDefaults();
    }

    protected static function applyDefaults()
    {
        $defaults = [
            'enabled' => '1',
            'rate_limit_per_minute' => '50',
            'rate_limit_per_hour' => '1000',
        ];

        foreach ($defaults as $key => $value) {
            if (!isset(self::$cache[$key])) {
                self::$cache[$key] = $value;
            }
        }
    }

    public static function clearCache()
    {
        self::$cache = null;
    }
}
