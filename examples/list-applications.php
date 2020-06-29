<?php
require_once '../vendor/autoload.php';

$client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET));

foreach ($client->applications() as $application) {
    echo sprintf("%s: %s\n", $application->getId(), $application->getName());
}
