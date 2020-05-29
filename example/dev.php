<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/../src/FilerDB.php';

// Instantiate Database
$filerdb = new FilerDB\Instance([ 'DATABASE_PATH' => __DIR__ . '/database/' ]);

// Query with filters, orders, and limits
$data = $filerdb
  ->database('test')
  ->collection('users')
  ->insert([
    'username' => 'etari2',
    'email' => 'matt@irate.dev',
    'expiresAt' => $filerdb->timestamp->days(10)
  ]);
