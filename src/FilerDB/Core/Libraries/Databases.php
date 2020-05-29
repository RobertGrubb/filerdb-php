<?php

namespace FilerDB\Core\Libraries;

use FilerDB\Core\Utilities\Error;
use FilerDB\Core\Utilities\FileSystem;

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
    if (is_null($config)) Error::throw('NO_CONFIG_PRESENT');

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
    if ($exists) Error::throw('DATABASE_EXISTS', "$database already exists");
    $created = FileSystem::createDirectory($this->path($database));
    if (!$created) Error::throw('DATABASE_NOT_CREATED', "$database was unable to be created");
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
    if (!$exists) Error::throw('DATABASE_NOT_EXIST', "$database does not exist");
    $removed = FileSystem::removeDirectory($this->path($database));
    if (!$removed) Error::throw('DATABASE_DELETE_FAILED', "$database was unable to be deleted");
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

  private function path ($database) {
    $path = $this->config->DATABASE_PATH . DIRECTORY_SEPARATOR . $database . DIRECTORY_SEPARATOR;
    return $path;
  }

}
