<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/FilerDB.php';

try {
  // Instantiate Database
  $filerdb = new FilerDB\Instance([

    // Required
    'root' => __DIR__ . '/database',

    // Optional configurations
    'includeTimestamps' => true,

    // Specify database
    'database' => 'todo',

    // Configs
    'createRootIfNotExist' => true,
    'createDatabaseIfNotExist' => true,
    'createCollectionIfNotExist' => true
  ]);

  return $filerdb;
} catch (Exception $e) {
  return false;
}
