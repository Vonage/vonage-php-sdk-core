<?php

use Nexmo\Client;
use Nexmo\SMS\Message\SMS;
use Nexmo\Client\Exception\Request;

//example of sending an sms using an API key / secret
require_once '../vendor/autoload.php';

define('API_KEY', getenv('API_KEY'));
define('API_SECRET', getenv('API_SECRET'));
define('NEXMO_TO', getenv('NEXMO_TO'));
define('NEXMO_FROM', getenv('NEXMO_FROM'));

//create client with api key and secret
$client = new Client(new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET));

//send message using simple api params
$response = $client->sms()->send(
    new SMS(NEXMO_TO, NEXMO_FROM, 'Test message from the Nexmo PHP Client')
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

$text = new SMS(NEXMO_TO, NEXMO_FROM, $longwinded);
$response = $client->sms()->send($text);
$data = $response->current();

echo "Sent message to " . $data->getTo() . ". Balance is now " . $data->getRemainingBalance() . PHP_EOL;
echo "Message was split into " . count($response) . " messages, those message ids are: " . PHP_EOL;
foreach ($response as $index => $data) {
    echo "Balance was " . $data->getRemainingBalance() . " after message " . $data->getMessageId() . " was sent." . PHP_EOL;
}

//an invalid request
try {
    $text = new SMS('not valid', NEXMO_FROM, $longwinded);
    $client->sms()->send($text);
} catch (Request $e) {
    //can still get the API response
    $data     = $e->getEntity(); // The parsed response as an array
    $request  = $client->sms()->getAPIResource()->getLastRequest(); //PSR-7 Request Object
    $response = $client->sms()->getAPIResource()->getLastRequest(); //PSR-7 Response Object
    $code     = $e->getCode(); //nexmo error code
    error_log($e->getMessage()); //nexmo error message
}
