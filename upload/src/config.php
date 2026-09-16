<?php

$config['db']['host'] = 'localhost';
$config['db']['port'] = '3306';
$config['db']['username'] = 'devuser';
$config['db']['password'] = 'devpass123';
$config['db']['dbname'] = 'xenforo';

$config['fullUnicode'] = true;
$config['development']['enabled'] = true;
$config['development']['defaultAddOn'] = 'chgold/AIConnect';

$config['enableAddOnArchiveInstaller'] = true;

// Dev override: grant all Pro bundles without a license key
putenv('AICONNECT_EDITION=pro');
$_ENV['AICONNECT_EDITION'] = 'pro';
