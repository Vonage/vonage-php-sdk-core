<?php

use Vonage\Application\Application;
use Vonage\Client;

require_once __DIR__ . '/vonage.php';

$client = new Client(new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

$a = new Application();

$a->setName('PHP Client Example');
$a->getVoiceConfig()->setWebhook('answer_url', 'https://example.com/answer', 'GET');
$a->getVoiceConfig()->setWebhook('event_url', 'https://example.com/event', 'POST');
$a->getMessagesConfig()->setWebhook('status_url', 'https://example.com/status', 'POST');
$a->getMessagesConfig()->setWebhook('inbound_url', 'https://example.com/inbound', 'POST');
$a->getRtcConfig()->setWebhook('event_url', 'https://example.com/event', 'POST');
$a->getVbcConfig()->enable();

$r = $client->applications()->create($a);

vonageDebug($r);
