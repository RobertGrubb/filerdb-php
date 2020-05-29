<?php

namespace FilerDB\Core\Utilities;

use FilerDB\Core\Exceptions\FilerDBException;

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
    if (is_null($config))
      throw new FilerDBException('No configuration found in Utilities\\Timetamp');

    // Set the configuration
    $this->config = $config;
  }

  /**
   * Date Methods
   */

  /**
   * Returns a timestamp for now
   */
  public function now () {
    return time();
  }

  /**
   * Returns a timestamp for however many days
   * specified (A negative number can be provided)
   */
  public function days ($days = 0) {
    return strtotime("$days days");
  }
}
