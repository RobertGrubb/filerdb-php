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

  $data = $filerdb
    ->database('test')
    ->collection('users')
    ->filter(['id' => '5ed1b2957c6d92'])
    ->update([
      'username' => 'test234'
    ]);

  var_dump($data);
} catch (Exception $e) {
  echo $e->getMessage() . PHP_EOL;
}
