<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/../src/FilerDB.php';

try {
  // Instantiate Database
  $filerdb = new FilerDB\Instance([

    // Required
    'path' => __DIR__ . '/database',

    // Optional configurations
    'includeTimestamps' => false,

    'createDatabaseIfNotExist' => true
  ]);

  // Query with filters, orders, and limits
  $data = $filerdb
    ->database('test')
    ->collection('users')
    ->insert([
      'username' => 'etari2',
      'email' => 'matt@irate.dev',
      'expiresAt' => $filerdb->timestamp->days(10)
    ]);
} catch (Exception $e) {
  echo $e->getMessage() . PHP_EOL;
}
