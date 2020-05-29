<?php

namespace FilerDB\Core\Libraries;

use FilerDB\Core\Utilities\FileSystem;

use FilerDB\Core\Exceptions\FilerDBException;

class Databases {

  /**
   * Database configuration
   * @var object
   */
  private $config;

  /**
   * Current databases
   * @var array
   */
  public $databases;

  /**
   * Class constructor
   */
  public function __construct ($config = null) {

    // If config is null, throw an error.
    if (is_null($config))
      throw new FilerDBException('No configuration found in Libarires\\Databases');

    // Set the configuration
    $this->config = $config;

    // Retrieve the current databases.
    $this->retrieveDatabases();
  }

  /**
   * ==============================
   * Basic methods
   * ==============================
   */

  /**
   * Checks if a specific database exists
   * @param  string $database
   * @return boolean
   */
  public function exists($database) {
    if (in_array($database, $this->databases)) return true;
    return false;
  }

  /**
   * List of databases
   * @return array databases
   */
  public function list() {
    return $this->databases;
  }


  /**
   * ==============================
   * Creation / Deletion Methods
   * ==============================
   */

  /**
   * Creates a new directory for the database in
   * the DATABASE_PATH
   * @param string $database
   */
  public function create($database) {
    $exists = $this->exists($database);
    if ($exists) throw new FilerDBException('Database already exists');
    $created = FileSystem::createDirectory($this->path($database));
    if (!$created) throw new FilerDBException('Database was unable to be created');
    $this->retrieveDatabases();
    return true;
  }

  /**
   * Delets a directory for the database in
   * the DATABASE_PATH
   * @param string $database
   */
  public function delete($database) {
    $exists = $this->exists($database);
    if (!$exists) throw new FilerDBException('Database does not exist');
    $removed = FileSystem::removeDirectory($this->path($database));
    if (!$removed) throw new FilerDBException('Database was unable to be deleted');
    $this->retrieveDatabases();
    return true;
  }

  /**
   * ==============================
   * Helper methods
   * ==============================
   */

  /**
   * Returns current databases
   * @return array
   */
  private function retrieveDatabases() {
    $result = [];
    $databases = glob($this->config->DATABASE_PATH . '*' , GLOB_ONLYDIR);

    foreach ($databases as $database) {
      $pathParts = explode('/', $database);
      $result[] = $pathParts[(count($pathParts) - 1)];
    }

    $this->databases = $result;
  }

  /**
   * Builds the path for the database in the file system.
   * @return string $path
   */
  private function path ($database) {
    $path = $this->config->DATABASE_PATH . DIRECTORY_SEPARATOR . $database . DIRECTORY_SEPARATOR;
    return $path;
  }

}
