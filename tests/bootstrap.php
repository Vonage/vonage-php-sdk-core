<?php
// Setup autoloading
$loader = require(__DIR__ . '/../vendor/autoload.php');
// Add Autoloading of test classes
$loader->add('Nexmo\\', __DIR__);