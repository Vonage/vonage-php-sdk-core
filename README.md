Nexmo Client Library for PHP 
============================

[Installation](#Installation) | [Usage](#Usage) |  [Examples](#Examples) | [Coverage](#API-Coverage) | [Contributing](#Contributing)  

This is the PHP client library for use Nexmo's API. To use this, you'll need a Nexmo account. Sign up [for free at 
nexmo.com][signup].

Installation
------------

To install the PHP client library using Composer:

    composer require nexmo/client

Alternatively you can clone the repository, however, you'll need to ensure the library is autoloaded by a PSR-0 or PSR-4
compatible autoloader.

    git clone git@github.com:Nexmo/nexmo-php.git

Usage
-----

If you're using composer, make sure the autoloader is included in your project's bootstrap file:

    require_once "vendor/autoload.php";
    
Create a client with your API key and secret:

    $client = new Nexmo\Client(new Nexmo\Credentials\Basic(API_KEY, API_SECRET));     

Examples
--------

### Sending A Message

To use [Nexmo's SMS API][doc_sms] to send an SMS message, call the `$client->message()->send()` method.

The API can be called directly, using a simple array of parameters, the keys match the [parameters of the API][doc_sms].

    $message = $client->message()->send([
        'to' => NEXMO_TO,
        'from' => NEXMO_FROM,
        'text' => 'Test message from the Nexmo PHP Client'
    ]);
    
The API response data can be accessed as array properties of the message. 

    echo "Sent message to " . $message['to'] . ". Balance is now " . $message['remaining-balance'] . PHP_EOL;
    
The message objects is a more expressive way to create and send messages. Each message type can be constructed with the 
required parameters, and a fluent interface provides access to optional parameters.

    $text = new \Nexmo\Message\Text(NEXMO_TO, NEXMO_FROM, 'Test message using PHP client library');
    $text->setClientRef('test-message')
         ->setClass(\Nexmo\Message\Text::CLASS_FLASH);

The message object is passed to the same `send` method:

    $client->message()->send($text);
    
Once sent, the message object can be used to access the response data.

    echo "Sent message to " . $text->getTo() . ". Balance is now " . $text->getRemainingBalance() . PHP_EOL;
    
Array access can still be used:

    echo "Sent message to " . $text['to'] . ". Balance is now " . $text['remaining-balance'] . PHP_EOL;
    
If the message text had to be sent as multiple messages, by default, the data of the last message is returned. However,
specific message data can be accessed using array notation, passing an index to a getter, or iterating over the object.

    $text[0]['remaining-balance']
    $text->getRemainingBalance(0);
    foreach($text as $index => $data){
        $data['remaining-balance'];
    }

The [send example][send_example] also has full working examples.

API Coverage
------------

* Account
    * [ ] Balance
    * [ ] Pricing
    * [ ] Settings
    * [ ] Top Up
    * [ ] Numbers
* Number
    * [ ] Search
    * [ ] Buy
    * [ ] Cancel
    * [ ] Update
* NumberInsight
    * [ ] Request
    * [ ] Response
* NumberVerify
    * [ ] Verify
    * [ ] Check
    * [ ] Search
    * [ ] Control
* Search
    * [ ] Message
    * [ ] Messages
    * [ ] Rejections
* Short Code
    * [ ] 2FA
    * [ ] Alerts
    * [ ] Marketing
* SMS
    * [X] Send
    * [ ] Receipt
    * [ ] Inbound
* Voice
    * [ ] Call
    * [ ] TTS/TTS Prompt
    * [ ] SIP

Contributing
------------

This library is currently being refactored from an earlier prototype to match the current [client library spec][spec].
The `legacy` branch can be used to require that earlier version. During the transition the `develop` and `master` 
branches will have both new and legacy code. The [API coverage](#API-Coverage) section identifies what features are 
currently implemented and up to date. 

To contribute to the library, docs, or examples, [create an issue][issues] or a pull request. Please only raise issues
about features marked as working in the [API coverage](#API-Coverage) as the rest of the code is being updated.

License
-------

This library is released under the [MIT License][license]

[signup]: http://nexmo.com
[doc_sms]: https://docs.nexmo.com/api-ref/sms-api
[license]: LICENSE.txt
[send_example]: examples/send.php
[spec]: https://github.com/Nexmo/client-library-specification