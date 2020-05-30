<?php

namespace FilerDB;

use FilerDB\Core\Exceptions\FilerDBException;

use FilerDB\Core\Utilities\Timestamp;
use FilerDB\Core\Utilities\FileSystem;

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
       * If the database path does not exist, try
       * and create it.
       */
      'createDatabaseIfNotExist' => false,

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
    $databasePath = FileSystem::databasePath($this->config->DATABASE_PATH);

    /**
     * If database path does not exist,
     * we have a few things we can do.
     */
    if (!FileSystem::pathExists($databasePath)) {

      // Make sure the config var is set to true.
      if ($this->config->createDatabaseIfNotExist === true) {

        // Attempt to create the directory
        $created = FileSystem::createDirectory($databasePath);

        // If not created, then a permissions error probably happened.
        if (!$created)
          throw new FilerDBException('Path not found, also unable to create database path.');
      } else {

        // Config not set, simply error.
        throw new FilerDBException('Database path not found');
      }
    }
  }

  /**
   * Initializes the database
   */
  private function _initialize () {
    if (!$this->config->DATABASE_PATH)
      throw new FilerDBException("No database path provided.");

    $this->databases  = new Databases($this->config);
    $this->timestamp = new Timestamp($this->config);

    // Check if the default database was provided in the config.
    $this->_checkDefaultDatabase();
  }

  /**
   * Start the chain with database.
   */
  public function database ($database) {
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
    if (!$this->defaultDatabase->collectionExists($collection))
      throw new FilerDBException("$collection does not exist");

    // Return a new collection instantiation.
    return new Collection($this->config, $this->config->database, $collection);
  }

  /**
   * Selects a default database for all collection calls
   * to go to.
   */
  public function selectDatabase ($database) {
    $exists = $this->databases->exists($this->config->database);

    if (!$exists)
      throw new FilerDBException($this->config->database . " not found");

    $this->defaultDatabase = new Database($this->config, $this->config->database);
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
          if (substr($val, -1) !== '/') $val = $val . '/';
          $this->set('DATABASE_PATH', $val);
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
