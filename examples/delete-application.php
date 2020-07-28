<?php

use Nexmo\Client;

require_once '../vendor/autoload.php';

$client = new Client(new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET));

try {
    $client->applications()->delete(MESSAGES_APPLICATION_ID);
    echo "Deleted application " . MESSAGES_APPLICATION_ID . PHP_EOL;
} catch (\Nexmo\Client\Exception\Request $e) {
    echo "There was a problem with the request: " . $e->getMessage() . PHP_EOL;
} catch (\Nexmo\Client\Exception\Server $e) {
    echo "The server encounted an error: " . $e->getMessage() . PHP_EOL;
}
