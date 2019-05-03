<?php

require_once '../vendor/autoload.php';

$client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic('7c9738e6', 'n3w-Api-key!'));

$a = $client->applications()->get('a8f93ca3-2a6f-4722-a53e-b7cccabc0bb9');

$a->getVoiceConfig()->setWebhook('answer_url', 'https://example.com/answer', 'GET');
$a->getVoiceConfig()->setWebhook('event_url', 'https://example.com/event', 'POST');
$a->getMessagesConfig()->setWebhook('status_url', 'https://example.com/status', 'POST');
$a->getMessagesConfig()->setWebhook('inbound_url', 'https://example.com/inbound', 'POST');
$a->getRtcConfig()->setWebhook('event_url', 'https://example.com/event', 'POST');
$a->getVbcConfig()->disable();

$client->applications()->update($a);
