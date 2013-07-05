<?php
//example of sending an sms using an API key / secret
require_once '../vendor/autoload.php';

$client = new Nexmo\Client(new Nexmo\Credentials\Basic('api_key', 'api_secret'));
$response = $client->sendSMS(new Nexmo\Message\Text('to', 'from', 'text'));

var_dump($response);