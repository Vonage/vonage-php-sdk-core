<?php

use Nexmo\Client;
use Nexmo\Client\Credentials\Keypair;

require_once "vendor/autoload.php";

$recordingId = 'RECORDING_ID';

$keypair = new Keypair(file_get_contents(__DIR__ . '/private.key'), 'APPLICATION_ID');
$client = new Client($keypair);
$recording = 'https://api.nexmo.com/v1/files/'.$recordingId;
$data = $client->get($recording);

file_put_contents($recordingId.'.mp3', $data->getBody());
