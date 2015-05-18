<?php

include '../vendor/autoload.php';

$addonWriter = new \AdamDBurton\GMad\AddonWriter('testing.gma');
$addonWriter->write(dirname(__FILE__) . '/testing/', [ 'materials/add.png', 'lua/autorun/server/test.lua' ], 'Test Addon', 'This is a test addon made by gmad-php' . "\n" . 'By adamdburton');

$addonReader = new \AdamDBurton\GMad\AddonReader('testing.gma');
$addonReader->parse();

print_r($addonReader);