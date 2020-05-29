<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/../src/FilerDB.php';

// Instantiate Database
$filerdb = new FilerDB\Instance([ 'DATABASE_PATH' => __DIR__ . '/database/' ]);

// Insert a new document
$data = $filerdb
  ->database('test')
  ->collection('users')
  ->filter([
    'username' => 'irate'
  ]);

print_r($data);
