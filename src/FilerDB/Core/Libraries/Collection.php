<?php

namespace FilerDB\Core\Libraries;

use FilerDB\Core\Utilities\Error;
use FilerDB\Core\Utilities\FileSystem;

use FilerDB\Core\Libraries\Document;

class Collection {

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
   * Current collection
   * @var array
   */
  private $collection = null;

  /**
   * Class constructor
   */
  public function __construct ($config = null, $database, $collection) {

    // If config is null, throw an error.
    if (is_null($config)) Error::throw('NO_CONFIG_PRESENT');

    // Set the configuration
    $this->config = $config;

    // Set the current database
    $this->database = $database;

    // Retrieve the current database.
    $this->collection = $collection;
  }

  /**
   * ==============================
   * Data retrieval methods
   * ==============================
   */

  public function get ($id) {
    $documents = $this->documents();
    if (!isset($documents->{$id})) return false;
    return $documents->{$id};
  }

  public function all () {
    $documents = $this->documents();
    return $documents;
  }

  /**
   * Filter results from the documents.
   */
  public function filter ($filters = []) {
    $documents = $this->documents();
    $filteredDocuments = (object) [];

    foreach ($documents as $id => $document) {
      $passes = true;

      foreach ($filters as $filter => $value) {

        if (!isset($document->{$filter})) {
          $passes = false;
          continue;
        }

        if ($document->{$filter} !== $value) {
          $passes = false;
          continue;
        }
      }

      if ($passes === true)
        $filteredDocuments->{$id} = $document;
    }

    return $filteredDocuments;
  }

  /**
   * ==============================
   * Data alteration methods
   * ==============================
   */

  /**
   * Insert data to the collection
   */
  public function insert ($data) {

    // Check if the data is a correct format
    if (!is_array($data) && !is_object($data))
      Error::throw('INSERT_DATA_ERROR', 'Must be an array or object');

    // Start insertData as an empty object
    $insertData = (object) [];

    // Iterate through the keys, add them to insertData
    foreach ($data as $key => $val) {
      $insertData->{$key} = $val;
    }

    // Get the current documents
    $documents = $this->documents();

    // If id is provided, or generate a unique id
    $id = (isset($insertData->id) ? $insertData->id : uniqid());

    // If the id already set?
    if (isset($documents->{$id}))
      Error::throw('INSERT_DATA_ERROR', "Document with id:$id already exists");

    // Add new data to the documents
    $documents->{$id} = $insertData;

    // Convert to json
    $json = json_encode($documents, JSON_PRETTY_PRINT);

    // Attempt to write file
    $inserted = FileSystem::writeFile($this->path(), $json);

    // If not inserted, throw an error.
    if (!$inserted)
      Error::throw('INSERT_DATA_ERROR', "Collection was unable to be overwrited");

    return true;
  }

  /**
   * ==============================
   * Helper methods
   * ==============================
   */

  /**
   * Get all documents in object format.
   * If the file can not be decoded, throw
   * an error because the data is malformed.
   */
  private function documents () {
    $contents = file_get_contents ($this->path());

    try {
      $contents = json_decode($contents);
    } catch (\Exception $e) {
      Error::throw('COLLECTION_READ_ERROR', "$this->collection.json is damaged");
    }

    return $contents;
  }

  /**
   * Returns a path for the current collection.
   */
  private function path () {
    $path = $this->config->DATABASE_PATH .
            DIRECTORY_SEPARATOR .
            $this->database .
            DIRECTORY_SEPARATOR .
            $this->collection . '.json';

    return $path;
  }
}
