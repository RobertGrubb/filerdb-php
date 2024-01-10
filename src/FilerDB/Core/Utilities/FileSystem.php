<?php

namespace FilerDB\Core\Utilities;

use FilerDB\Core\Exceptions\FilerDBException;

class FileSystem
{

  public static function rootPath($path)
  {
    if (substr($path, -1) !== '/') $path = $path . DIRECTORY_SEPARATOR;
    return $path;
  }

  public static function databasePath($path, $database)
  {
    return $path . $database . DIRECTORY_SEPARATOR;
  }

  /**
   * Returns collection path for the file system.
   * @param  string $root
   * @param  string $database
   * @param  string $collection
   * @return string
   */
  public static function collectionPath($root, $database, $collection)
  {
    $path = $root .
      $database .
      DIRECTORY_SEPARATOR .
      $collection . '.json';

    return $path;
  }

  /**
   * ==============================
   * File Methods
   * ==============================
   */

  /**
   * Attempts to write a file in the filesystem.
   * @param  string $file
   * @param  mixed $data
   * @return boolean
   */
  public static function writeFile($file, $data)
  {
    try {
      file_put_contents($file, $data);
    } catch (Exception $e) {
      throw new FilerDBException($file . " could not be created");
      return false;
    }

    return true;
  }

  /**
   * Deletes a file in the filesystem, throws an error
   * if an exception is encountered.
   */
  public static function deleteFile($file)
  {
    try {
      unlink($file);
    } catch (Exception $e) {
      throw new FilerDBException($file . " was unable to be deleted");
      return false;
    }

    return true;
  }


  /**
   * ==============================
   * Path Methods
   * ==============================
   */

  /**
   * Checks if path exists
   * @param  string $dir
   * @return boolean
   */
  public static function pathExists($dir)
  {
    if (!file_exists($dir)) {
      return false;
    }

    return true;
  }

  /**
   * Checks if src is writable
   * @param  string $src
   * @return boolean
   */
  public static function isWritable($src)
  {
    if (is_writable($src)) {
      return false;
    }

    return true;
  }

  /**
   * Attempts to make a directory
   * @param  string $dir
   * @return boolean
   */
  public static function createDirectory($dir)
  {
    if (!mkdir($dir, 0777)) {
      return false;
    }

    return true;
  }

  /**
   * Removes a directory and all of it's contents
   * @param  string $src
   * @return boolean
   */
  public static function removeDirectory($src)
  {
    $dir = opendir($src);

    while (false !== ($file = readdir($dir))) {
      if (($file != '.') && ($file != '..')) {
        $full = $src . '/' . $file;
        if (is_dir($full)) rmdir($full);
        else unlink($full);
      }
    }

    closedir($dir);
    rmdir($src);
    return true;
  }
}
