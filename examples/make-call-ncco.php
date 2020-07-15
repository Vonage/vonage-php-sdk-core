<?php

use Nexmo\Client;
use Nexmo\Voice\Webhook;
use Nexmo\Voice\OutboundCall;
use Nexmo\Voice\Endpoint\Phone;
use Nexmo\Client\Credentials\Keypair;

require_once '../vendor/autoload.php';

$keypair = new Keypair(
    file_get_contents(NEXMO_APPLICATION_PRIVATE_KEY_PATH),
    NEXMO_APPLICATION_ID
);
$client = new Client($keypair);

$outboundCall = new OutboundCall(
    new Phone(TO_NUMBER),
    new Phone(NEXMO_NUMBER)
);
$outboundCall->setAnswerWebhook(
    new Webhook(
        'https://developer.nexmo.com/ncco/tts.json',
        Webhook::METHOD_GET
    )
);
$response = $client->voice()->createOutboundCall($outboundCall);

var_dump($response);
