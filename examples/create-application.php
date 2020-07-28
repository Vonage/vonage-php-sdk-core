<?php

use Nexmo\Client;
use Nexmo\Application\Application;

require_once '../vendor/autoload.php';

$client = new Client(new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET));

$a = new Application();

$a->setName('PHP Client Example');
$a->getVoiceConfig()->setWebhook('answer_url', 'https://example.com/answer', 'GET');
$a->getVoiceConfig()->setWebhook('event_url', 'https://example.com/event', 'POST');
$a->getMessagesConfig()->setWebhook('status_url', 'https://example.com/status', 'POST');
$a->getMessagesConfig()->setWebhook('inbound_url', 'https://example.com/inbound', 'POST');
$a->getRtcConfig()->setWebhook('event_url', 'https://example.com/event', 'POST');
$a->getVbcConfig()->enable();

$r = $client->applications()->create($a);

print_r($r);
