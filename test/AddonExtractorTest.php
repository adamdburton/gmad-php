<?php

/**
 * @coversDefaultClass \AdamDBurton\GMad\AddonReader
 */
class AddonExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function testCompact()
    {
        $addonWriter = new \AdamDBurton\GMad\AddonWriter('testing.gma');
        $this->assertTrue($addonWriter->write(dirname(__FILE__) . '/testing/', [ 'materials/add.png', 'lua/autorun/server/test.lua' ], 'Test Addon', 'This is a test addon made by gmad-php' . "\n" . 'By adamdburton'));
    }

    public function testExtract()
    {
        $addonReader = new \AdamDBurton\GMad\AddonReader('test2.gma');
        $this->assertTrue($addonReader->parse());
    }
}
