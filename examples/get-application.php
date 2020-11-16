<?php

use Vonage\Client;

require_once __DIR__ . '/vonage.php';

$client = new Client(new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

$a = $client->applications()->get(APPLICATION_ID);
echo $a->getName() . PHP_EOL;

echo "\nPUBLIC KEY\n-----\n";
echo $a->getPublicKey();

echo "\nVOICE\n-----\n";
echo $a->getVoiceConfig()->getWebhook('answer_url') . PHP_EOL;
echo $a->getVoiceConfig()->getWebhook('event_url') . PHP_EOL;

echo "\nMessages\n-----\n";
echo $a->getMessagesConfig()->getWebhook('inbound_url') . PHP_EOL;
echo $a->getMessagesConfig()->getWebhook('status_url') . PHP_EOL;

echo "\nRTC\n-----\n";
echo $a->getRtcConfig()->getWebhook('event_url') . PHP_EOL;

echo "\nVBC\n-----\n";
echo $a->getVbcConfig()->isEnabled() ? 'Enabled' : 'Disabled';
echo "\n\n";
