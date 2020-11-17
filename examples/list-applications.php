<?php

use Vonage\Client;

require_once __DIR__ . '/vonage.php';

$client = new Client(new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

foreach ($client->applications() as $application) {
    echo sprintf("%s: %s\n", $application->getId(), $application->getName());
}
