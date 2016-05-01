<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

// Setup autoloading
$loader = require(__DIR__ . '/../vendor/autoload.php');
// Add Autoloading of test classes
$loader->addPsr4('NexmoTest\\', __DIR__);