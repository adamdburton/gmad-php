<?php

class AddonExtractorTest extends \PHPUnit_Framework_TestCase
{
    private static $filename;
    private static $directory;

    public static function setUpBeforeClass()
    {
        self::$filename = dirname(__FILE__) . '/testing.gma';
        self::$directory = dirname(__FILE__) . '/testing/';

        if(file_exists(self::$filename))
        {
            unlink(self::$filename);
        }
    }

    public function testCompact()
    {
        $addonWriter = new \AdamDBurton\GMad\AddonWriter(self::$filename);

        $this->assertTrue($addonWriter->write(self::$directory, [ 'materials/add.png', 'lua/autorun/server/test.lua' ], 'Test Addon', 'This is a test addon made by gmad-php' . "\n" . 'By adamdburton'));
        $this->assertTrue(file_exists(self::$filename));
    }

    public function testExtract()
    {
        $addonReader = new \AdamDBurton\GMad\AddonReader(self::$filename);
        $this->assertTrue($addonReader->parse());
    }

    public static function tearDownAfterClass()
    {
        if(file_exists(self::$filename))
        {
            unlink(self::$filename);
        }
    }
}
