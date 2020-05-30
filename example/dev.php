<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/../src/FilerDB.php';

try {
  // Instantiate Database
  $filerdb = new FilerDB\Instance([

    // Required
    'path' => __DIR__ . '/database2',

    // Optional configurations
    'includeTimestamps' => false,

    'database' => 'woot',

    // Configs
    'createRootIfNotExist' => true,
    'createDatabaseIfNotExist' => true,
    'createCollectionIfNotExist' => true
  ]);

  $filerdb->collection('foo')->insert([
    'testing' => true
  ]);

} catch (Exception $e) {
  echo $e->getMessage() . PHP_EOL;
}
