<?php

namespace FilerDB;

use FilerDB\Core\Exceptions\FilerDBException;

use FilerDB\Core\Utilities\Timestamp;
use FilerDB\Core\Utilities\FileSystem;
use FilerDB\Core\Utilities\Backup;

use FilerDB\Core\Libraries\Database;
use FilerDB\Core\Libraries\Databases;
use FilerDB\Core\Libraries\Collection;

class Instance
{

  /**
   * Default configuration
   */
  private $config = [];

  /**
   * Database Statuses
   */
  private $status = [
    'DATABASE_IS_WRITABLE' => false
  ];

  /**
   * Database instance holder
   * @var FilerDB\Core\Libraries\Databases
   */
  public $databases = null;

  /**
   * Database instance holder for the selected
   * database in the config.
   * @var FilerDB\Core\Libraries\Database
   */
  public $defaultDatabase = null;

  /**
   * Collection instance holder
   * NOTE: This is only available if a database is selected in
   * the configuration
   * @var FilerDB\Core\Libraries\Collection
   */
  public $collection = null;

  /**
   * Timestamp instance holder
   * @var FilerDB\Core\Libraries\Timestamp
   */
  public $timestamp = null;

  /**
   * Backup instance holder
   * @var FilerDB\Core\Libraries\Backuop
   */
  public $backup = null;

  /**
   * Class constructor
   */
  public function __construct ($config = null) {

    /**
     * Set the initial configuration variables.
     */
    $this->_setInitialConfig([

      /**
       * This is the main path for FilerDB.
       */
      'path' => false,

      /**
       * If the root path does not exist, try
       * and create it.
       */
      'createRootIfNotExist' => false,

      /**
       * If the database path does not exist, try
       * and create it.
       */
      'createDatabaseIfNotExist' => false,

      /**
       * If the collection does not exist, attempt
       * to create it.
       */
      'createCollectionIfNotExist' => false,

      /**
       * If the insert and update logic handles
       * the createdAt and updatedAt timestamps
       * automatically
       */
      'includeTimestamps' => false

    ], $config);

    /**
     * Begins core checks, refer to method for
     * more information.
     */
    $this->_runCoreChecks();

    /**
     * Initialize everything
     */
    $this->_initialize();
  }

  /**
   * Handles required checks to make sure
   * the database is in good standing.
   *
   * Checks for things like path existence, etc.
   */
  private function _runCoreChecks () {

    // Builds the path for the database.
    $rootPath = FileSystem::rootPath($this->config->DATABASE_PATH);

    /**
     * If database path does not exist,
     * we have a few things we can do.
     */
    if (!FileSystem::pathExists($rootPath)) {

      // Make sure the config var is set to true.
      if ($this->config->createRootIfNotExist === true) {

        // Attempt to create the directory
        $created = FileSystem::createDirectory($rootPath);

        // If not created, then a permissions error probably happened.
        if (!$created)
          throw new FilerDBException('Path not found, also unable to create database path.');
      } else {

        // Config not set, simply error.
        throw new FilerDBException('Root path not found');
      }
    }
  }

  /**
   * Initializes the database
   */
  private function _initialize () {
    if (!$this->config->DATABASE_PATH)
      throw new FilerDBException("No database path provided.");

    $this->databases = new Databases($this->config);
    $this->backup    = new Backup($this->config);
    $this->timestamp = new Timestamp($this->config);

    // Check if the default database was provided in the config.
    $this->_checkDefaultDatabase();
  }

  /**
   * Start the chain with database.
   */
  public function database ($database) {
    if (!$this->databases->exists($database)) {

      /**
       * If set to create when non-existent
       */
      if ($this->config->createDatabaseIfNotExist === true) {

        // Get the database path
        $databasePath = FileSystem::databasePath(
          $this->config->DATABASE_PATH,
          $database
        );

        // Attempt to create the directory
        $created = FileSystem::createDirectory($databasePath);

        // If not created, then a permissions error probably happened.
        if (!$created)
          throw new FilerDBException('Database not found, also unable to create database path.');

      } else {
        throw new FilerDBException('Database does not exist.');
      }
    }

    return new Database($this->config, $database);
  }

  /**
   * FilerDB now supports the ability to select a default
   * database. So we can now instantiate the collection class
   * below so we can skip the ->database portion of the logic.
   *
   * NOTE: You can still access the ->database logic if you need
   * to pull from a different database within the same code.
   */
  public function collection ($collection) {

    // If default database is not set, error.
    if (!$this->defaultDatabase)
      throw new FilerDBException("A default database must be selected");

    // If the collection does not exist
    if (!$this->defaultDatabase->collectionExists($collection)) {

      // If the collection does not exist, and config says to attempt
      // to create it, do that here.
      if ($this->config->createCollectionIfNotExist === true) {
        $collectionPath = FileSystem::collectionPath(
          $this->config->DATABASE_PATH,
          $this->config->database,
          $collection
        );

        // Attempt to create the directory
        $created = FileSystem::writeFile($collectionPath, json_encode([]));

        // If not created, then a permissions error probably happened.
        if (!$created)
          throw new FilerDBException('Path not found, also unable to create database path.');

      } else {
        throw new FilerDBException("$collection does not exist");
      }
    }

    // Return a new collection instantiation.
    return new Collection($this->config, $this->config->database, $collection);
  }

  /**
   * Selects a default database for all collection calls
   * to go to.
   */
  public function selectDatabase ($database = null) {
    $database = !is_null($database) ? $database : $this->config->database;

    if (!$database) throw new FilerDBException('No database provided');

    if (!$this->databases->exists($database)) {

      /**
       * If set to create when non-existent
       */
      if ($this->config->createDatabaseIfNotExist === true) {

        // Get the database path
        $databasePath = FileSystem::databasePath(
          $this->config->DATABASE_PATH,
          $database
        );

        // Attempt to create the directory
        $created = FileSystem::createDirectory($databasePath);

        // If not created, then a permissions error probably happened.
        if (!$created)
          throw new FilerDBException('Database not found, also unable to create database path.');

      } else {
        throw new FilerDBException('Database does not exist.');
      }
    }

    $this->defaultDatabase = new Database($this->config, $database);
  }

  /**
   * Ran from instantiation. Will select a default database
   * if one is provided in the configuration
   */
  private function _checkDefaultDatabase () {
    if (isset($this->config->database)) {
      if (!is_null($this->config->database) && !empty($this->config->database)) {
        $this->selectDatabase($this->config->database);
      }
    }
  }

  /**
   * Get a configuration variable from the
   * class configuration.
   */
  private function _get ($var) {
    if (isset($this->config->{$var})) return $var;
    return false;
  }

  /**
   * Sets the default class configuration
   */
  private function _setInitialConfig ($initialConfig, $config) {
    $this->config = (object) $initialConfig;

    if (!is_null($config)) {
      if (!is_array($config)) return false;

      foreach ($config as $key => $val) {

        // Make sure path sets DATABASE_PATH for
        // backwards compatibility.
        if ($key === 'path' || $key === 'DATABASE_PATH') {
          $this->set('DATABASE_PATH', FileSystem::rootPath($val));
        } else {
          $this->set($key, $val);
        }
      }
    }

    return $this;
  }

  /**
   * Set the class configuration variable.
   */
  public function set ($var, $val) {
    $this->config->{$var} = $val;
  }

}
