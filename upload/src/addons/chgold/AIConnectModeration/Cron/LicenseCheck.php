<?php

namespace chgold\AIConnectModeration\Cron;

class LicenseCheck
{
    public static function run(): void
    {
        \chgold\AIConnectModeration\License\Validator::check(false);
    }
}
