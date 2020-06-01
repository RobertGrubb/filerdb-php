<?php

require_once __DIR__ , '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/FilerDB.php';

try {
  // Instantiate Database
  $filerdb = new FilerDB\Instance([

    // Required
    'path' => __DIR__ . '/database',
  ]);

  /**
   * How to create a zip backup of your database.
   *
   * @param $file
   *
   * This is the file you want to output your database
   * backup to.
   */
  $filerdb->backup->create(__DIR__ . '/backup.zip');

} catch (Exception $e) {
  echo $e->getMessage() . PHP_EOL;
}
