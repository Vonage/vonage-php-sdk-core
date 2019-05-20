<?php
require_once '../vendor/autoload.php';

$keypair = new \Nexmo\Client\Credentials\Keypair(
    file_get_contents('/Users/michael/development/nexmo/empty-voice-project/private.key'),
    '75196d98-d3a0-476c-9fbc-7df9aa326aff'
);

$client = new \Nexmo\Client($keypair);

use Nexmo\Call\Call;
$call = new Call();
$call->setTo('447908249481')
     ->setFrom('123456')
  ->setNcco([
        [
            'action' => 'talk',
            'text' => 'This is a text to speech call from Nexmo'
        ]
      ]);

$response = $client->calls()->create($call);

echo $response->getId();
