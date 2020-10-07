<?php

use Vonage\Client;
use Vonage\Client\Credentials\Keypair;

require_once __DIR__ . '/vonage.php';

$recordingId = 'RECORDING_ID';

$keypair = new Keypair(APPLICATION_SECRET, APPLICATION_ID);
$client = new Client($keypair);
$recording = 'https://api.nexmo.com/v1/files/' . $recordingId;
$data = $client->get($recording);

file_put_contents($recordingId . '.mp3', $data->getBody());
