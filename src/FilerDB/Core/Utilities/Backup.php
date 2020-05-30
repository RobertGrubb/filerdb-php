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

  /**
   * Makes use of PhpZip to create a zipped backup file
   * of the database path.
   * @param  string $output
   * @return boolean|exception
   */
  public function create ($output = './backup.zip') {
    $zipFile = new ZipFile();

    try {

      $zipFile
        ->addDirRecursive($this->config->root)
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
