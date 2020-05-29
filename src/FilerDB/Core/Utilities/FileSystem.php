<?php

namespace FilerDB\Core\Utilities;

use FilerDB\Core\Utilities\Error;

class FileSystem {

  /**
   * ==============================
   * File Methods
   * ==============================
   */

  public static function writeFile ($file, $data) {
    try {
      file_put_contents($file, $data);
    } catch (Exception $e) {
      Error::throw('FILE_CREATE_FAIL');
      return false;
    }

    return true;
  }

  public static function deleteFile ($file) {
    try {
      unlink($file);
    } catch (Exception $e) {
      Error::throw('FILE_DELETE_FAIL');
      return false;
    }

    return true;
  }


  /**
   * ==============================
   * Path Methods
   * ==============================
   */

  public static function pathExists ($dir) {
    if (!file_exists($dir)) {
      return false;
    }

    return true;
  }

  public static function isWritable ($src) {
    if (is_writable($src)) {
      return false;
    }

    return true;
  }

  public static function createDirectory ($dir) {
    if (!mkdir($dir, 0777)) {
      return false;
    }

    return true;
  }

  public static function removeDirectory ($src) {
    $dir = opendir($src);

    while(false !== ( $file = readdir($dir)) ) {
      if (($file != '.') && ($file != '..')) {
        $full = $src . '/' . $file;
        if (is_dir($full)) rrmdir($full);
        else unlink($full);
      }
    }

    closedir($dir);
    rmdir($src);
    return true;
  }
}
