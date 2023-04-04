<?php

namespace FilerDB\Tests;

use FilerDB\Instance;
use PHPUnit\Framework\TestCase;

class FilerDBTest extends TestCase
{
    /**
     * @var FilerDB\Instance
     */
    private static $filerdb;

    // Path to the example database directory
    private static $databaseDir = __DIR__ . '/../example/database';

    public static function setUpBeforeClass(): void
    {

        self::$filerdb = new Instance([

            // Required
            'path' => self::$databaseDir,
        ]);
    }

    /**
     * Make sure the database list comes back as
     * an array.
     */
    public function testDatabaseList()
    {
        $list = self::$filerdb->databases->list();
        $this->assertEquals(true, is_array($list));
    }
}
