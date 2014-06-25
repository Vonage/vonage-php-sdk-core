Nexmo PHP Client Library
========================

## Setup

Currently requires Guzzle via [Composer](https://github.com/composer/composer). 
  
    $ composer install

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

## Number Insight

Create a new request:

`$request = new Nexmo\Network\Number\Request(NUMBER, CALLBACK_URL);`

Send the request, and get a response:

    $response = $client->send($request);
    if($response->isError(){
        // handle error
    }
    
    $id = $response->getId(); // id of request
    
Process an inbound callback

    try{
        $callback = Nexmo\Network\Number\Callback::fromEnv();
    } catch (Exception $e) {
        error_log('not a valid NI callback: ' . $e->getMessage());
        return;
    }

    if($callback->hasType()){
        echo $callback->getNumber() . ' is a ' . $callback->getType() . ' number';
    }
    
Combine request and callback data

    $response = $memcached->get($callback->id());
    
    // this will create a new response object with both the API response data, and the callback data (appending the 
    // callback data if another callback has already been added to the response)
    $response = Nexmo\Network\Number\Request::addCallback($response, $callback);
    
    if($response->isComplete()){
        //store the data
    }