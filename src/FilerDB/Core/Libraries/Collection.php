<?php

namespace FilerDB\Core\Libraries;

use FilerDB\Core\Exceptions\FilerDBException;

// Utilities
use FilerDB\Core\Utilities\FileSystem;
use FilerDB\Core\Utilities\Timestamp;
use FilerDB\Core\Utilities\Dot;

// Helpers
use FilerDB\Core\Helpers\Document;

class Collection
{

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
   * The output of documents
   */
  private $response = [];

  /**
   * Holds the path for the collection in the filesystem.
   * @var string
   */
  private $collectionPath = null;

  /**
   * Class constructor
   */
  public function __construct($config, $database, $collection)
  {

    // If config is null, throw an error.
    if (is_null($config))
      throw new FilerDBException('No configuration found in Libarires\\Collection');

    // Set the configuration
    $this->config = $config;

    // Instantiate new timestamp instance
    $this->timestamp = new Timestamp($this->config);

    // Set the current database
    $this->database = $database;

    // Retrieve the current collection.
    $this->collection = $collection;

    // Build the collection path
    $this->collectionPath = FileSystem::collectionPath(
      $this->config->root,
      $this->database,
      $this->collection
    );

    // Holder for documents that should be returned
    $this->documents = $this->getDocuments();

    // Holder for response that is returned
    $this->response  = $this->documents;
  }

  /**
   * ==============================
   * Data retrieval methods
   * ==============================
   */

  /**
   * Grabs a document with a specific id
   */
  public function id($id)
  {
    $documents = $this->documents;
    $data = Document::byId($documents, $id);

    if ($data === false) {
      $this->documents = false;
      $this->response = false;
      return $this;
    }

    $this->documents = $documents[$data->index];
    $this->response  = $this->documents;
    return $this;
  }

  /**
   * Returns documents after filters, limits
   * orders, and anything else that chains
   * before it.
   */
  public function get($fields = false)
  {

    // Make sure there is a document(s) to iterate through.
    if ($this->response !== false) {

      /**
       * If the columns parameter is provided,
       * and is an array.
       */
      if (is_array($fields)) {
        if (count($fields) >= 1) {
          return $this->pickFieldsFromData($this->response, $fields);
        }
      }
    }

    return $this->response;
  }

  /**
   * Returns number of documents
   */
  public function count()
  {
    return count($this->response);
  }

  /**
   * Returns all documents in the collection.
   */
  public function all()
  {
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
  public function filter($filters = [])
  {
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

          /**
           * If filter is a dot notation, handle it
           * properly.
           */
          if (Dot::test($filter) === true) {

            $dotVal = Dot::get($document, $filter);

            if (!$dotVal) {
              $passes = false;
              continue;
            }

            if ($dotVal !== $value) {
              $passes = false;
              continue;
            }
          } else {

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

            // Will be set based on following conditional
            $documentValue = null;

            /**
             * If the field is dot notated, handle it
             * properly.
             */
            if (Dot::test($field) === true) {

              // Get the dot notation value
              $dotVal = Dot::get($document, $field);

              // If false, skip this.
              if (!$dotVal) {
                $passes = false;
                continue;
              }

              // Set the document value.
              $documentValue = $dotVal;
            } else {

              if (!isset($document->{$field})) {
                $passes = false;
                continue;
              }

              $documentValue = $document->{$field};
            }

            /**
             * Equals conditional
             */
            if ($conditional === '=') {

              if ($documentValue == $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }

              /**
               * Greater than or equal to
               */
            } else if ($conditional === '>=') {

              if ($documentValue >= $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }

              /**
               * Greater than
               */
            } elseif ($conditional === '>') {

              if ($documentValue > $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }

              /**
               * Less than or equal to
               */
            } elseif ($conditional === '<=') {

              if ($documentValue <= $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }

              /**
               * Less than
               */
            } elseif ($conditional === '<') {

              if ($documentValue < $value) {
                $passes = true;
              } else {
                $passes = false;
                continue;
              }
            } else {
              $passes = false;
            }
          } else {
          }
        }
      }

      if ($passes === true)
        $filteredDocuments[] = $document;
    }

    // Set documents to the filtered documents.
    $this->documents = $filteredDocuments;

    // Set the response as the same documents
    $this->response  = $filteredDocuments;

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
  public function insert($data)
  {

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
    if (Document::exists($documents, $id))
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
    $inserted = FileSystem::writeFile($this->collectionPath, $json);

    // If not inserted, throw an error.
    if (!$inserted)
      throw new FilerDBException("Collection was unable to be overwrited");

    return true;
  }

  /**
   * Update data to the collection
   */
  public function update($data)
  {
    $documentsToUpdate = $this->documents;
    $originalDocuments = $this->getDocuments();

    // If there is a document to update.
    if ($documentsToUpdate) {

      // If the documentsToUpdate is not an array,
      // we can assume it's a single document that is being
      // updated.
      if (!is_array($documentsToUpdate)) {
        $docInfo = Document::byId($originalDocuments, $this->documents->id);
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
    $updated = FileSystem::writeFile($this->collectionPath, $json);

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
  public function empty()
  {
    $documents = [];

    // Convert to json
    $json = json_encode($documents, JSON_PRETTY_PRINT);

    // Attempt to write file
    $emptied = FileSystem::writeFile($this->collectionPath, $json);

    // If not deleted, throw an error.
    if (!$emptied)
      throw new FilerDBException("Collection was unable to be emptied");

    return true;
  }

  /**
   * Will delete all documents that have been filtered.
   */
  public function delete()
  {
    $documentsToDelete = $this->documents;
    $originalDocuments = $this->getDocuments();

    /**
     * Filter out records that match the documents
     * to be deleted.
     */
    if (is_array($documentsToDelete)) {
      foreach ($documentsToDelete as $deleteDoc) {
        foreach ($originalDocuments as $key => $origDoc) {
          if ($origDoc->id === $deleteDoc->id) {
            unset($originalDocuments[$key]);
          }
        }
      }
    } else {
      foreach ($originalDocuments as $key => $origDoc) {
        if ($origDoc->id === $documentsToDelete->id) {
          unset($originalDocuments[$key]);
        }
      }
    }

    // Rebase the array element keys.
    $originalDocuments = array_values($originalDocuments);

    // Convert to json
    $json = json_encode($originalDocuments, JSON_PRETTY_PRINT);

    // Attempt to write file
    $deleted = FileSystem::writeFile($this->collectionPath, $json);

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
  public function orderBy($field, $direction = 'asc')
  {
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

    // Set the response
    $this->response = $documents;

    // Return this instance
    return $this;
  }

  /**
   * Limit the number of results that are returned
   */
  public function limit($limit, $offset = 0)
  {
    $documents = $this->documents;
    $limitedDocuments = [];

    if ($offset > count($documents)) {
      $this->response = [];
      return $this;
    }

    for ($i = $offset; $i < ($limit + $offset); $i++) {
      // For those times when the limit is higher than document count.
      if (!isset($documents[$i])) continue;

      $limitedDocuments[] = $documents[$i];
    }

    /**
     * One of the only methods to actually alter ONLY the
     * response. Because of offsetting, we need an original
     * documents array, as well as one to keep track of the
     * offsetting.
     */
    $this->response = $limitedDocuments;

    // Return instance.
    return $this;
  }

  /**
   * ==============================
   * Helper methods
   * ==============================
   */

  /**
   * Takes in an object, or an array of objects,
   * and returns ONLY the fields specified in $fields
   * @param  array|object $data
   * @param  array
   * @return array|object
   */
  private function pickFieldsFromData($data, $fields)
  {

    if (is_array($data)) {
      foreach ($data as $documentKey => $document) {
        foreach ($document as $field => $value) {
          if (!in_array($field, $fields)) unset($data[$documentKey]->{$field});
        }
      }
    } else {
      foreach ($data as $field => $val) {
        if (!in_array($field, $fields)) unset($data->{$field});
      }
    }

    return $data;
  }

  /**
   * Get all documents in object format.
   * If the file can not be decoded, throw
   * an error because the data is malformed.
   */
  private function getDocuments()
  {
    $contents = file_get_contents($this->collectionPath);

    try {
      $contents = json_decode($contents);
    } catch (\Exception $e) {
      throw new FilerDBException("$this->collection.json is damaged");
    }

    return $contents;
  }
}
