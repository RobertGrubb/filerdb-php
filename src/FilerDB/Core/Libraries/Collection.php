<?php

namespace FilerDB\Core\Libraries;

use FilerDB\Core\Exceptions\FilerDBException;

// Utilities
use FilerDB\Core\Utilities\FileSystem;
use FilerDB\Core\Utilities\Timestamp;

// Libraries
use FilerDB\Core\Libraries\Document;

class Collection {

  /**
   * Database configuration
   * @var object
   */
  private $config;

  /**
   * Timestamp instance holder
   */
  private $timestamp;

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
    if (is_null($config))
      throw new FilerDBException('No configuration found in Libarires\\Collection');

    // Set the configuration
    $this->config = $config;

    // Instantiate new timestamp instance
    $this->timestamp = new Timestamp($this->config);

    // Set the current database
    $this->database = $database;

    // Retrieve the current database.
    $this->collection = $collection;

    // Holder for documents that should be returned
    $this->documents = $this->getDocuments();
  }

  /**
   * ==============================
   * Data retrieval methods
   * ==============================
   */

  /**
   * Grabs a document with a specific id
   */
  public function id ($id) {
    $documents = $this->documents;
    $data = $this->documentById($id, $documents);
    if ($data === false) return false;
    $this->documents = $documents[$data->index];
    return $this;
  }

  /**
   * Returns documents after filters, limits
   * orders, and anything else that chains
   * before it.
   */
  public function get () {
    return $this->documents;
  }

  /**
   * Returns number of documents
   */
  public function count () {
    return count($this->documents);
  }

  /**
   * Returns all documents in the collection.
   */
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

          // If the field is not set.
          if (!isset($document->{$filter})) {
            $passes = false;
            continue;
          }

          // If conditional does not match.
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
            throw new FilerDBException('Format must be an array with a field, conditional, and value.');
          }
        }
      }

      if ($passes === true)
        $filteredDocuments[] = $document;
    }

    // Set documents to the filtered documents.
    $this->documents = $filteredDocuments;

    // Return this instance for chaining.
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
      throw new FilerDBException('Insert data must be an array or object');

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
      throw new FilerDBException("Document with id:$id already exists");

    $insertData->id = $id;

    /**
     * If the configuration allows it, set the
     * createdAt and updatedAt timestamps
     */
    if ($this->config->includeTimestamps === true) {
      $insertData->createdAt = $this->timestamp->now();
      $insertData->updatedAt = $this->timestamp->now();
    }

    // Add new data to the documents
    $documents[] = $insertData;

    // Convert to json
    $json = json_encode($documents, JSON_PRETTY_PRINT);

    // Attempt to write file
    $inserted = FileSystem::writeFile($this->path(), $json);

    // If not inserted, throw an error.
    if (!$inserted)
      throw new FilerDBException("Collection was unable to be overwrited");

    return true;
  }

  /**
   * Update data to the collection
   */
  public function update ($data) {
    $documentsToUpdate = $this->documents;
    $originalDocuments = $this->getDocuments();

    // If there is a document to update.
    if ($documentsToUpdate) {

      // If the documentsToUpdate is not an array,
      // we can assume it's a single document that is being
      // updated.
      if (!is_array($documentsToUpdate)) {
        $docInfo = $this->documentById($this->documents->id, $originalDocuments);
        $key = $docInfo->index;

        // Update all of the keys
        foreach ($data as $field => $value) {
          $originalDocuments[$key]->{$field} = $value;
        }

        if ($this->config->includeTimestamps)
          $originalDocuments[$key]->updatedAt = $this->timestamp->now();

      // If it is an array, then it means we have multiple documents
      // that are in need of updating. Iterate through each and
      // update accordingly.
      } else {

        /**
         * Filter out records that match the documents
         * to be deleted.
         */
        foreach ($documentsToUpdate as $updateDoc) {
          foreach ($originalDocuments as $key => $origDoc) {
            if ($origDoc->id === $updateDoc->id) {
              foreach ($data as $field => $value) {
                $originalDocuments[$key]->{$field} = $value;
              }

              if ($this->config->includeTimestamps)
                $originalDocuments[$key]->updatedAt = $this->timestamp->now();
            }
          }
        }
      }
    } else {

      // Return false here because nothing needs
      // to be updated.
      return false;
    }

    // Rebase the array element keys.
    $originalDocuments = array_values($originalDocuments);

    // Convert to json
    $json = json_encode($originalDocuments, JSON_PRETTY_PRINT);

    // Attempt to write file
    $updated = FileSystem::writeFile($this->path(), $json);

    // If not deleted, throw an error.
    if (!$updated)
      throw new FilerDBException("Collection was unable to be overwrited");

    /**
     * @TODO:
     *
     * Think about returning a "X documents updated" return.
     */
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
      throw new FilerDBException("Collection was unable to be emptied");

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
      throw new FilerDBException("Please use ->empty() to delete all records");
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
      throw new FilerDBException("Collection was unable to be overwrited");

    return true;
  }

  /**
   * ==============================
   * Data limits and orders
   * ==============================
   */

  /**
   * Orders by a field (asc, or desc).
   * @TODO: Add ability to order by deep nested array/object
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

    // Update the documents
    $this->documents = $documents;

    // Return this instance
    return $this;
  }

  /**
   * Limit the number of results that are returned
   */
  public function limit($limit, $offset = 0) {
    $documents = $this->documents;
    $limitedDocuments = (object) [];

    for ($i = 0; $i < $limit; $i++) {
      $limitedDocuments[] = $documents[$i];
    }

    // Set the documents to the limited documents
    $this->documents = $limitedDocuments;

    // Return instance.
    return $this;
  }

  /**
   * ==============================
   * Helper methods
   * ==============================
   */

  /**
   * Find a document by it's id in an array
   * of documents.
   *
   * Returns the index and the document data in
   * object format.
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
      throw new FilerDBException("$this->collection.json is damaged");
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
