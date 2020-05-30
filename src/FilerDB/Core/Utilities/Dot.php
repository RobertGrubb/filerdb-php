<?php

namespace FilerDB\Core\Utilities;

class Dot {

  /**
   * Test if a path is a dot notation
   */
  public static function test ($path) {
    if (strpos($path, '.') !== false) return true;
    return false;
  }

  /**
   * Gets value from an object based on
   * dot notation.
   *
   * Example: location.state
   */
  public static function get($object, $path) {
    $parts = explode('.', $path);
    $obj = $object;

    foreach ($parts as $param) {
      if (!isset($obj->{$param})) return false;
      $obj = $obj->{$param};
    }

    return $obj;
  }

}
