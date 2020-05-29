<?php

namespace FilerDB\Core\Utilities;

use FilerDB\Core\Exceptions\FilerDBException;

class Error {

  public static function throw($code, $message = null) {
    $errorMessage = 'Unknown error';

    switch ($code) {
      case 'NO_DATABASE_PATH':
        $errorMessage = 'No database path present';
        break;

      case 'NO_CONFIG_PRESENT':
        $errorMessage = 'No configuration present';
        break;

      case 'DOCUMENT_NOT_EXIST':
        $errorMessage = 'The document does not exist';
        break;

      case 'DOCUMENT_EXISTS':
        $errorMessage = 'The document already exists';
        break;

      case 'DOCUMENT_CREATE_FAIL':
        $errorMessage = $message || 'Document creation failed';
        break;

      default:
        $errorMessage = $message || $errorMessage;
        break;
    }

    throw new FilerDBException(json_encode([
      'error' => true,
      'code'  => $code,
      'message' => $errorMessage
    ]));
  }

}
