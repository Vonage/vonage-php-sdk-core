Nexmo PHP Client Library
========================

## Creating a Client

Client currently supports OAuth, or API credentials:

`$client = new Nexmo\Client(new Nexmo\Credentials\Basic(API_KEY, API_SECRET));`

`$client = new Nexmo\Client(new Nexmo\Credentials\OAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_SECRET));`

## Making a Request

Nexmo supports multiple types of SMS message, any can be passed to `$client->sendSMS()` to send the message.

`$response = $client->sendSMS(new Nexmo\Message\Text(NEXMO_TO, NEXMO_FROM, 'Test message from the Nexmo PHP Client'));`

## Checking Response Status

Larger messages may be broken into multiple parts.

`$messages = $response->getMessages();`

The response object also allows iteration over each part:

    foreach($response as $message){
        $message->getStatus();
    }