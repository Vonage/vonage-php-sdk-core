<?php

require_once '../vendor/autoload.php';

$client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET));

try {
    $isDeleted = $client->applications()->delete(MESSAGES_APPLICATION_ID);

    if ($isDeleted) {
        echo "Deleted application " . MESSAGES_APPLICATION_ID . PHP_EOL;
    } else {
        echo "Could not delete application " . MESSAGES_APPLICATION_ID . PHP_EOL;
    }
} catch (\Nexmo\Client\Exception\Request $e) {
    echo "There was a problem with the request: " . $e->getMessage() . PHP_EOL;
} catch (\Nexmo\Client\Exception\Server $e) {
    echo "The server encounted an error: " . $e->getMessage() . PHP_EOL;
}

