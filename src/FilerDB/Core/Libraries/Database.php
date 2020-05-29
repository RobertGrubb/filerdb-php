<?php

namespace FilerDB\Core\Libraries;

use FilerDB\Core\Exceptions\FilerDBException;

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
    if (is_null($config))
      throw new FilerDBException('No configuration found in Libarires\\Database');

    // Set the configuration
    $this->config = $config;

    // Retrieve the current database.
    $this->database = $database;
  }

  /**
   * Instantiates the collection class for this
   * database. Also throws an error if the collection
   * does not exist.
   *
   * @TODO: Create config variable that decides whether or not
   * it auto creates the collection if it doesn't exist.
   */
  public function collection($collection) {
    if (!$this->collectionExists($collection))
      throw new FilerDBException('Collection does not exist');

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
    if ($exists) throw new FilerDBException('Collection already exists');
    $created = FileSystem::writeFile($collectionPath, json_encode([]));
    if (!$created) throw new FilerDBException('Collection was unable to be created');
    return true;
  }

  /**
   * Delets a collection for a database
   * @param string $collection
   */
  public function deleteCollection($collection) {
    $collectionPath = $this->path() . $collection . '.json';
    $exists = $this->collectionExists($collection);
    if (!$exists) throw new FilerDBException('Collection does not exist');
    $removed = FileSystem::deleteFile($collectionPath);
    if (!$removed) throw new FilerDBException('Collection was unable to be deleted');
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

  /**
   * Builds the path for the database in the file system.
   * @return string $path
   */
  private function path () {
    $path = $this->config->DATABASE_PATH . DIRECTORY_SEPARATOR . $this->database . DIRECTORY_SEPARATOR;
    return $path;
  }

}
