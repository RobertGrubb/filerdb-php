<?php

namespace FilerDB\Core\Utilities;

use \PhpZip\ZipFile;

class Backup {

  /**
   * Database configuration
   * @var object
   */
  private $config;

  /**
   * Class constructor
   */
  public function __construct ($config = null) {

    // If config is null, throw an error.
    if (is_null($config))
      throw new FilerDBException('No configuration found in Utilities\\Timetamp');

    // Set the configuration
    $this->config = $config;
  }

  public function create ($output = './backup.zip') {
    $zipFile = new ZipFile();

    var_dump($this->config->DATABASE_PATH);
    var_dump($output);

    try {

      $zipFile
        ->addDirRecursive($this->config->DATABASE_PATH)
        ->saveAsFile($output)
        ->close();

    } catch(\PhpZip\Exception\ZipException $e) {
      throw new FilerDBException($e->getMessage());
    } finally {
      $zipFile->close();
    }

    return true;
  }

}
