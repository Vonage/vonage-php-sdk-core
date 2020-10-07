<?php

use Vonage\Client;
use Vonage\SMS\Message\SMS;

require_once __DIR__ . '/vonage.php';

//create client with api key and secret
$client = new Client(new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

//send message using simple api params
$response = $client->sms()->send(
    new SMS(VONAGE_TO, VONAGE_FROM, 'Test message from the Nexmo PHP Client')
);

//array access provides response data
$data = $response->current();
echo "Sent message to " . $data->getTo() . ". Balance is now " . $data->getRemainingBalance() . PHP_EOL;

sleep(1);

//sending a message over 160 characters
$longwinded = <<<EOF
But soft! What light through yonder window breaks?
It is the east, and Juliet is the sun.
Arise, fair sun, and kill the envious moon,
Who is already sick and pale with grief,
That thou, her maid, art far more fair than she.
EOF;

$text = new SMS(VONAGE_TO, VONAGE_FROM, $longwinded);
$response = $client->sms()->send($text);
$data = $response->current();

echo "Sent message to " . $data->getTo() . ". Balance is now " . $data->getRemainingBalance() . PHP_EOL;
echo "Message was split into " . count($response) . " messages, those message ids are: " . PHP_EOL;

foreach ($response as $index => $data) {
    echo "Balance was " . $data->getRemainingBalance() .
        " after message " . $data->getMessageId() . " was sent." . PHP_EOL;
}

//an invalid request
try {
    $text = new SMS('not valid', VONAGE_FROM, $longwinded);
    $client->sms()->send($text);
} catch (Exception $e) {
    echo "The server encountered an error: " . PHP_EOL;

    vonageDebug($e);
}
