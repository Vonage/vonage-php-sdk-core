<?php

require_once '../vendor/autoload.php';

$client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET));

$a = $client->applications()->get(APPLICATION_ID);

$client->applications()->delete($a);

