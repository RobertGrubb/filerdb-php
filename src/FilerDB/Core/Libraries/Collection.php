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
   * Documents for collection
   */
  private $documents = [];

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

    $this->documents = $this->getDocuments();
  }

  /**
   * ==============================
   * Data retrieval methods
   * ==============================
   */

  public function id ($id) {
    $documents = $this->documents;
    $data = $this->documentById($id, $documents);
    if ($data === false) return false;
    return $documents[$data->index];
  }

  public function get () {
    return $this->documents;
  }

  public function all () {
    $documents = $this->documents;
    return $documents;
  }

  /**
   * ==============================
   * Filter methods
   * ==============================
   */

  /**
   * Filter results from the documents.
   */
  public function filter ($filters = []) {
    $documents = $this->documents;
    $filteredDocuments = [];

    foreach ($documents as $document) {
      $passes = true;

      foreach ($filters as $filter => $value) {

        /**
         * If value is not an array, then it's a simple
         * key/value filter.
         */
        if (!is_array($value)) {
          if (!isset($document->{$filter})) {
            $passes = false;
            continue;
          }

          if ($document->{$filter} !== $value) {
            $passes = false;
            continue;
          }
        }

        /**
         * If is array, then it must match the following:
         * ['field', '>', 'value']
         */
        if (is_array($value)) {

          /**
           * If value length
           */
          if (count($value) === 3) {

            // Setup variables
            $field = $value[0];
            $conditional = $value[1];
            $value = $value[2];

            if (!isset($document->{$field})) {
              $passes = false;
              continue;
            }

            /**
             * Equals conditional
             */
            if ($conditional === '=') {

              if ($document->{$field} == $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }

            /**
             * Greater than or equal to
             */
            } else if ($conditional === '>=') {

              if ($document->{$field} >= $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }

            /**
             * Greater than
             */
            } elseif ($conditional === '>') {

              if ($document->{$field} > $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }

            /**
             * Less than or equal to
             */
            } elseif ($conditional === '<=') {

              if ($document->{$field} <= $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }

            /**
             * Less than
             */
            } elseif ($conditional === '<') {

              if ($document->{$field} < $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }
            } else {
              $passes = false;
            }
          } else {
            Error::throw('FILTER_ERROR', 'Format must be an array with a field, conditional, and value.');
          }
        }
      }

      if ($passes === true)
        $filteredDocuments[] = $document;
    }

    $this->documents = $filteredDocuments;

    return $this;
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
    $documents = $this->documents;

    // If id is provided, or generate a unique id
    $id = (isset($insertData->id) ? $insertData->id : uniqid());

    // If the id already set?
    if ($this->documentById($id, $documents) !== false)
      Error::throw('INSERT_DATA_ERROR', "Document with id:$id already exists");

    $insertData->id = $id;

    // Add new data to the documents
    $documents[] = $insertData;

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
   * Will delete all documents.
   */
  public function empty () {
    $documents = [];

    // Convert to json
    $json = json_encode($documents, JSON_PRETTY_PRINT);

    // Attempt to write file
    $emptied = FileSystem::writeFile($this->path(), $json);

    // If not deleted, throw an error.
    if (!$emptied)
      Error::throw('EMPTY_ERROR', "Collection was unable to be emptied");

    return true;
  }

  /**
   * Will delete all documents that have been filtered.
   */
  public function delete () {
    $documentsToDelete = $this->documents;
    $originalDocuments = $this->getDocuments();

    /**
     * Fail safe, we do not want to delete all records
     * with this method.
     *
     * Warn them to use ->empty() instead.
     */
    if (count($documentsToDelete) === count($originalDocuments)) {
      Error::throw('DELETE_ALL_WARNING', 'Please use ->empty() to delete all records');
      return false;
    }

    /**
     * Filter out records that match the documents
     * to be deleted.
     */
    foreach ($documentsToDelete as $deleteDoc) {
      foreach ($originalDocuments as $key => $origDoc) {
        if ($origDoc->id === $deleteDoc->id) {
          unset($originalDocuments[$key]);
        }
      }
    }

    // Rebase the array element keys.
    $originalDocuments = array_values($originalDocuments);

    // Convert to json
    $json = json_encode($originalDocuments, JSON_PRETTY_PRINT);

    // Attempt to write file
    $deleted = FileSystem::writeFile($this->path(), $json);

    // If not deleted, throw an error.
    if (!$deleted)
      Error::throw('DELETE_ERROR', "Collection was unable to be overwrited");

    return true;
  }

  /**
   * ==============================
   * Data limits and orders
   * ==============================
   */

  public function orderBy($field, $direction = 'asc') {
    $documents = $this->documents;

    if ($direction === 'asc') {
      usort($documents, function ($item1, $item2) use ($field) {
        return $item1->{$field} <=> $item2->{$field};
      });
    } elseif ($direction === 'desc') {
      usort($documents, function ($item1, $item2) use ($field) {
        return $item2->{$field} <=> $item1->{$field};
      });
    }

    $this->documents = $documents;

    return $this;
  }

  public function limit($limit) {
    $documents = $this->documents;
    $limitedDocuments = (object) [];

    $count = 0;
    foreach ($documents as $id => $document) {
      if ($count >= $limit) continue;
      $limitedDocuments->{$id} = $document;
      $count++;
    }

    $this->documents = $limitedDocuments;
    return $this;
  }

  /**
   * ==============================
   * Helper methods
   * ==============================
   */

  private function documentById ($id, $documents) {
    $index = false;
    $doc = false;

    foreach ($documents as $i => $document) {

      if ($document->id === $id) {
        $index = $i;
        $doc = $document;
      } else {
        continue;
      }
    }

    if (!$index) return false;

    return (object) [
      'index' => $index,
      'document' => $doc
    ];
  }

  /**
   * Get all documents in object format.
   * If the file can not be decoded, throw
   * an error because the data is malformed.
   */
  private function getDocuments () {
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
