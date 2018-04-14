<?php

if(class_exists('PHPUnit_Framework_Assert')) {
    class_alias('PHPUnit_Framework_Assert', 'PHPUnit\Framework\Assert');
}

require 'vendor/autoload.php';
