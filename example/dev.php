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

  /**
   * Example of limiting to 1 response, and
   * offsetting it.
   *
   * limit(1, 1)
   *
   * @param Limiter
   * @param Offset
   *
   * Offset is the array key of the response.
   */
  $data = $filerdb
    ->database('test')
    ->collection('users')
    ->limit(1, 1)
    ->get();

  print_r($data);
} catch (Exception $e) {
  echo $e->getMessage() . PHP_EOL;
}
