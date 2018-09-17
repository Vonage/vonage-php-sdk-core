<?php
//example of searching for a number to buy, sending a SMS from it and then cancelling the bought number
//This example works only for confirmed NEXMO account. Free trials account will fail.

require_once '../vendor/autoload.php';

const API_KEY = '';
const API_SECRET = '';
const NEXMO_TO = '';

//create client with api key and secret
$client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET));

// search for french numbers
echo ">>> Searching for Numbers...".PHP_EOL;
try {
    //search for French numbers to buy
    $collection = $client->numbers()->search('FR', null, null, Nexmo\Numbers\Number::FEATURE_SMS);

    foreach ($collection as $index => $number) {
        echo sprintf("[%d] Number: %s – Cost: %0.2f", $index, $number->getId(), $number->getCost()) . PHP_EOL;
    }
} catch (\Exception $e) {
    echo ">>> Retrieving numbers list failed :(".PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    exit;
}

$id = $collection[0]->getId();
echo PHP_EOL.sprintf(">>> Buying number %s", $id).PHP_EOL;
// let's buy the first one
try {
    $client->numbers()->buy('fr', $collection[0]->getId());
} catch (\Exception $e) {
    echo sprintf(">>> Buying number %s failed :(", $id).PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    exit;
}

// Let's send a SMS from this number
echo PHP_EOL.sprintf(">>> Sending a SMS from %s to %s", $id, NEXMO_TO).PHP_EOL;
try {
    $client->message()->sendText(NEXMO_TO, $id, 'Hey this is a text message from the freshly bought number');
} catch (\Exception $e) {
    echo sprintf(">>> Sending text failed :(", $id).PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    exit;
}

// Now let's cancel this number immediately
echo PHP_EOL.sprintf(">>> Cancelling number %s", $id).PHP_EOL;
try {
    $client->numbers()->cancel('fr', $id);
} catch (\Exception $e) {
    echo sprintf(">>> Cancelling number %s failed :(", $id).PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
    exit;
}

