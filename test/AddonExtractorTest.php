<?php

/**
 * @coversDefaultClass \AdamDBurton\GMad\AddonReader
 */
class AddonExtractorTest extends \PHPUnit_Framework_TestCase
{
    public function testExtract()
    {
        $addonReader = new \AdamDBurton\GMad\AddonReader('test.gma');
        $this->assertTrue($addonReader->parse());

        print_r($addonReader);
    }
}
