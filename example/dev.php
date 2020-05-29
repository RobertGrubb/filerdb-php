<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/../src/FilerDB.php';

// Instantiate Database
$filerdb = new FilerDB\Instance([
  'path' => __DIR__ . '/database/',
  'database' => 'test'
]);

// Query with filters, orders, and limits
$data = $filerdb
  ->collection('users')
  ->insert([
    'username' => 'etari2',
    'email' => 'matt@irate.dev',
    'expiresAt' => $filerdb->timestamp->days(10)
  ]);
