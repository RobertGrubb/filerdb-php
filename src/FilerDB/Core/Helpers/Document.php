<?php

namespace FilerDB\Core\Helpers;

use FilerDB\Core\Exceptions\FilerDBException;

class Document
{

    /**
     * Check documents to see if the id exists.
     * @param  array
     * @param  string
     * @return boolean
     */
    public static function exists($documents, $id)
    {
        if (self::byId($documents, $id) === false) {
            return false;
        }

        return true;
    }

    /**
     * Iterates through an array of documents, and finds
     * the object that has a specific id. It will then
     * return an object with the index, and the data
     * of that document.
     * @param  array $documents
     * @param  string $id
     * @return mixed
     */
    public static function byId($documents, $id = null)
    {
        if (is_null($id)) {
            return false;
        }

        // Set the index to false by default
        $index = false;

        // Set the doc to false by default.
        $doc = false;

        // Iterate through documents, continue if no match.
        foreach ($documents as $i => $document) {

            if ($document->id === $id) {
                $index = $i;
                $doc = $document;
            } else {
                continue;
            }
        }

        // If no index was set, return false.
        if ($index === false) {
            return false;
        }

        // Return object with data.
        return (object) [
            'index' => $index,
            'document' => $doc,
        ];
    }
}
