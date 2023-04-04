<?php

namespace FilerDB\Core\Libraries;

use FilerDB\Core\Exceptions\FilerDBException;
use FilerDB\Core\Libraries\Collection;
use FilerDB\Core\Utilities\FileSystem;

class Database
{

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
     * Holds the database path in the filesystem
     * @var string
     */
    private $databasePath = null;

    /**
     * Class constructor
     */
    public function __construct($config, $database)
    {

        // If config is null, throw an error.
        if (is_null($config)) {
            throw new FilerDBException('No configuration found in Libarires\\Database');
        }

        // Set the configuration
        $this->config = $config;

        // Retrieve the current database.
        $this->database = $database;

        // Build the database path
        $this->databasePath = FileSystem::databasePath($this->config->root, $this->database);
    }

    /**
     * Instantiates the collection class for this
     * database. Also throws an error if the collection
     * does not exist.
     *
     * @TODO: Create config variable that decides whether or not
     * it auto creates the collection if it doesn't exist.
     */
    public function collection($collection)
    {

        // If the collection does not exist
        if (!$this->collectionExists($collection)) {

            // If the collection does not exist, and config says to attempt
            // to create it, do that here.
            if ($this->config->createCollectionIfNotExist === true) {

                // Build the collection path
                $collectionPath = FileSystem::collectionPath(
                    $this->config->root,
                    $this->database,
                    $collection
                );

                // Attempt to create the directory
                $created = FileSystem::writeFile($collectionPath, json_encode([]));

                // If not created, then a permissions error probably happened.
                if (!$created) {
                    throw new FilerDBException('Path not found, also unable to create database path.');
                }

            } else {
                throw new FilerDBException("$collection does not exist");
            }
        }

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
    public function createCollection($collection)
    {
        $collectionPath = $this->databasePath . $collection . '.json';
        $exists = $this->collectionExists($collection);
        if ($exists) {
            throw new FilerDBException('Collection already exists');
        }

        $created = FileSystem::writeFile($collectionPath, json_encode([]));
        if (!$created) {
            throw new FilerDBException('Collection was unable to be created');
        }

        return true;
    }

    /**
     * Delets a collection for a database
     * @param string $collection
     */
    public function deleteCollection($collection)
    {
        $collectionPath = $this->databasePath . $collection . '.json';
        $exists = $this->collectionExists($collection);
        if (!$exists) {
            throw new FilerDBException('Collection does not exist');
        }

        $removed = FileSystem::deleteFile($collectionPath);
        if (!$removed) {
            throw new FilerDBException('Collection was unable to be deleted');
        }

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
    public function collections()
    {
        return $this->retrieveCollections();
    }

    /**
     * Checks if a collection exists
     * @return boolean
     */
    public function collectionExists($collection)
    {
        $collections = $this->retrieveCollections();
        if (in_array($collection, $collections)) {
            return true;
        }

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
    private function retrieveCollections()
    {
        $result = [];
        $collections = glob($this->databasePath . '*.json', GLOB_BRACE);

        foreach ($collections as $collection) {
            $result[] = basename($collection, '.json');
        }

        return $result;
    }

}
