<?php

use Vonage\Client;

require_once __DIR__ . '/vonage.php';

$client = new Client(new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

try {
    $client->applications()->delete(APPLICATION_ID);
    echo "Deleted application " . APPLICATION_ID . PHP_EOL;
} catch (Exception $e) {
    echo "The server encountered an error: " . PHP_EOL;

    vonageDebug($e);
}
