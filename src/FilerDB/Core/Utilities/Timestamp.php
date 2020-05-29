<?php

namespace FilerDB\Core\Utilities;

use FilerDB\Core\Utilities\Error;

class Timestamp {

  /**
   * Database configuration
   * @var object
   */
  private $config;

  /**
   * Class constructor
   */
  public function __construct ($config = null) {

    // If config is null, throw an error.
    if (is_null($config)) Error::throw('NO_CONFIG_PRESENT');

    // Set the configuration
    $this->config = $config;
  }

  /**
   * Date Methods
   */

  public function now () {
    return time();
  }

  public function days ($days = 0) {
    return strtotime("$days days");
  }
}
