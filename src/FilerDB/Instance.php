<?php

namespace FilerDB;

use FilerDB\Core\Utilities\Error;

use FilerDB\Core\Libraries\Database;
use FilerDB\Core\Libraries\Databases;

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
   * @var FilerDB\Core\Libraries\Database
   */
  public $databases = null;

  /**
   * Class constructor
   */
  public function __construct($config = null) {

    /**
     * Set the initial configuration variables.
     */
    $this->_setInitialConfig([
      'DATABASE_PATH' => false
    ], $config);

    /**
     * Initialize everything
     */
    $this->_initialize();
  }

  /**
   * Initializes the database
   */
  private function _initialize() {
    if (!$this->config->DATABASE_PATH) Error::throw('NO_DATABASE_PATH');
    $this->databases  = new Databases($this->config);
  }

  public function database($database) {
    return new Database($this->config, $database);
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
        $this->set($key, $val);
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
