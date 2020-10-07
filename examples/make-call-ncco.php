<?php

use Vonage\Client;
use Vonage\Client\Credentials\Keypair;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\OutboundCall;
use Vonage\Voice\Webhook;

require_once __DIR__ . '/vonage.php';

$keypair = new Keypair(APPLICATION_SECRET, APPLICATION_ID);
$client = new Client($keypair);

$outboundCall = new OutboundCall(
    new Phone(VONAGE_TO),
    new Phone(VONAGE_FROM)
);

$outboundCall->setAnswerWebhook(
    new Webhook(
        'https://developer.nexmo.com/ncco/tts.json',
        Webhook::METHOD_GET
    )
);

try {
    $response = $client->voice()->createOutboundCall($outboundCall);

    vonageDebug($response);
} catch (Exception $e) {
    echo "The server encountered an error: " . PHP_EOL;

    vonageDebug($e);
}
