<?php

namespace chgold\AIConnectPro\Cron;

class LicenseCheck
{
    public static function run(): void
    {
        \chgold\AIConnectPro\License\Validator::check(false);
    }
}
