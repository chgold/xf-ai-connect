<?php

namespace chgold\AIConnectAdmin\Cron;

class LicenseCheck
{
    public static function run(): void
    {
        \chgold\AIConnectAdmin\License\Validator::check(false);
    }
}
