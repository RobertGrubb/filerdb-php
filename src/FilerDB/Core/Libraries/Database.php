<?php

namespace FilerDB\Core\Libraries;

use FilerDB\Core\Utilities\Error;
use FilerDB\Core\Utilities\FileSystem;

use FilerDB\Core\Libraries\Collection;

class Database {

  /**
   * Database configuration
   * @var object
   */
  private $config;

  /**
   * Current database
   * @var array
   */
  public $database;

  /**
   * Class constructor
   */
  public function __construct ($config = null, $database) {

    // If config is null, throw an error.
    if (is_null($config)) Error::throw('NO_CONFIG_PRESENT');

    // Set the configuration
    $this->config = $config;

    // Retrieve the current database.
    $this->database = $database;
  }

  public function collection($collection) {
    if (!$this->collectionExists($collection))
      Error::throw('COLLECTION_NOT_EXIST', "$collection does not exist");

    return new Collection($this->config, $this->database, $collection);
  }

  /**
   * ==============================
   * Creation / Deletion Methods
   * ==============================
   */

  /**
   * Creates a new collection for a database
   * @param string $database
   */
  public function createCollection($collection) {
    $collectionPath = $this->path() . $collection . '.json';
    $exists = $this->collectionExists($collection);
    if ($exists) Error::throw('COLLECTION_EXISTS', "$collection already exists");
    $created = FileSystem::writeFile($collectionPath, json_encode([]));
    if (!$created) Error::throw('COLLECTION_NOT_CREATED', "$collection was unable to be created");
    return true;
  }

  /**
   * Delets a collection for a database
   * @param string $collection
   */
  public function deleteCollection($collection) {
    $collectionPath = $this->path() . $collection . '.json';
    $exists = $this->collectionExists($collection);
    if (!$exists) Error::throw('COLLECTION_NOT_EXIST', "$collection does not exist");
    $removed = FileSystem::deleteFile($collectionPath);
    if (!$removed) Error::throw('COLLECTION_DELETE_FAILED', "$collection was unable to be deleted");
    return true;
  }

  /**
   * ==============================
   * Basic methods
   * ==============================
   */

  /**
   * List of collections
   * @return array collections
   */
  public function collections() {
    return $this->retrieveCollections();
  }

  /**
   * Checks if a collection exists
   * @return boolean
   */
  public function collectionExists ($collection) {
    $collections = $this->retrieveCollections();
    if (in_array($collection, $collections)) return true;
    return false;
  }

  /**
   * ==============================
   * Helper methods
   * ==============================
   */

  /**
   * Returns collections for current database.
   * @return array
   */
  private function retrieveCollections() {
    $result = [];
    $collections = glob($this->path() . '*.json' , GLOB_BRACE);

    foreach ($collections as $collection) {
      $result[] = basename($collection, '.json');
    }

    return $result;
  }

  private function path () {
    $path = $this->config->DATABASE_PATH . DIRECTORY_SEPARATOR . $this->database . DIRECTORY_SEPARATOR;
    return $path;
  }

}
