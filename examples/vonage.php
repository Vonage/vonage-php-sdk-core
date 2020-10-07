<?php
require_once '../vendor/autoload.php';

if (!defined('API_KEY')) {
    define('API_KEY', '');
}

if (!defined('API_SECRET')) {
    define('API_SECRET', '');
}

if (!defined('APPLICATION_ID')) {
    define('APPLICATION_ID', '');
}

if (!defined('APPLICATION_SECRET')) {
    define('APPLICATION_SECRET', file_get_contents(__DIR__ . '/private.key'));
}

if (!defined('VONAGE_TO')) {
    define('VONAGE_TO', '');
}

if (!defined('VONAGE_FROM')) {
    define('VONAGE_FROM', '');
}

// Helper functions

/**
 * Helper method to output debug data for all passed variables,
 * uses `print_r()` for arrays and objects, `var_dump()` otherwise.
 *
 * @noinspection ForgottenDebugOutputInspection
 */
function vonageDebug()
{
    echo "<pre>";

    $args = func_get_args();
    $length = count($args);

    if ($length === 0) {
        echo "ERROR: No arguments provided.<hr>";
    } else {
        foreach ($args as $i => $iValue) {
            $arg = $iValue;

            echo "<h2>Argument {$i} (" . gettype($arg) . ")</h2>";

            if (is_array($arg) || is_object($arg)) {
                print_r($arg);
            } else {
                var_dump($arg);
            }

            echo "<hr>";
        }
    }

    $backtrace = debug_backtrace();

    // output call location to help finding these debug outputs again
    echo "vonageDebug() called in {$backtrace[0]['file']} on line {$backtrace[0]['line']}";

    echo "</pre>";

    exit;
}
