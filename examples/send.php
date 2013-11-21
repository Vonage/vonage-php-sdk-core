<?php
//example of sending an sms using an API key / secret
require_once '../vendor/autoload.php';

$client = new Nexmo\Client(new Nexmo\Credentials\Basic(API_KEY, API_SECRET));
//$client->setSecret(SIGNATURE_SECRET); //optional secret for signing requests

//a valid request
$response = $client->sendSMS(new Nexmo\Message\Text(NEXMO_TO, NEXMO_FROM, 'Test message from the Nexmo PHP Client'));
checkResponse($response);

//an invalid request
$response = $client->sendSMS(new Nexmo\Message\Text('invalid', NEXMO_FROM, 'Test message from the Nexmo PHP Client'));
checkResponse($response);

function checkResponse($response)
{
    //long messages are split into multiple messages, each with its own data
    foreach($response as $message){
        //status of 0 is success
        if($message->getStatus() == 0){
            echo "Sent message to " . $message->getTo() . ". Balance is now " . $message->getBalance() . PHP_EOL;
        //error message available if status is not success
        } else {
            echo "Error sending message, code: " . $message->getStatus() . " [" . $message->getErrorMessage() . "]" . PHP_EOL;
        }
    }
}
