Nexmo Client Library for PHP 
============================
[![Build Status](https://api.travis-ci.org/Nexmo/nexmo-php.svg?branch=master)](https://travis-ci.org/Nexmo/nexmo-php)

This is the PHP client library for use Nexmo's API. To use this, you'll need a Nexmo account. Sign up [for free at 
nexmo.com][signup]. This is currently a beta release, see [contributing](#contributing) for more information.

 * [Installation](#installation)
 * [Usage](#usage)
 * [Examples](#examples)
 * [Coverage](#api-coverage)
 * [Contributing](#contributing) 

Installation
------------

To use the client library you'll need to have [created a Nexmo account][signup]. 

To install the PHP client library using Composer:

```bash
composer require nexmo/client
```

Alternatively you can clone the repository, however, you'll need to ensure the library is autoloaded by a PSR-0 or PSR-4
compatible autoloader.

```bash
git clone git@github.com:Nexmo/nexmo-php.git
```

Usage
-----

If you're using composer, make sure the autoloader is included in your project's bootstrap file:

```php
require_once "vendor/autoload.php";
```
    
Create a client with your API key and secret:

```php
$client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET));     
```

Examples
--------

### Sending A Message

To use [Nexmo's SMS API][doc_sms] to send an SMS message, call the `$client->message()->send()` method.

The API can be called directly, using a simple array of parameters, the keys match the [parameters of the API][doc_sms].

```php
$message = $client->message()->send([
    'to' => NEXMO_TO,
    'from' => NEXMO_FROM,
    'text' => 'Test message from the Nexmo PHP Client'
]);
```
    
The API response data can be accessed as array properties of the message. 

```php
echo "Sent message to " . $message['to'] . ". Balance is now " . $message['remaining-balance'] . PHP_EOL;
```
    
**A message object** is a more expressive way to create and send messages. Each message type can be constructed with the 
required parameters, and a fluent interface provides access to optional parameters.

```php
$text = new \Nexmo\Message\Text(NEXMO_TO, NEXMO_FROM, 'Test message using PHP client library');
$text->setClientRef('test-message')
     ->setClass(\Nexmo\Message\Text::CLASS_FLASH);
```

The message object is passed to the same `send` method:

```php
$client->message()->send($text);
```
    
Once sent, the message object can be used to access the response data.

```php
echo "Sent message to " . $text->getTo() . ". Balance is now " . $text->getRemainingBalance() . PHP_EOL;
```
    
Array access can still be used:

```php
echo "Sent message to " . $text['to'] . ". Balance is now " . $text['remaining-balance'] . PHP_EOL;
```
    
If the message text had to be sent as multiple messages, by default, the data of the last message is returned. However,
specific message data can be accessed using array notation, passing an index to a getter, or iterating over the object.

```php
$text[0]['remaining-balance']
$text->getRemainingBalance(0);
foreach($text as $index => $data){
    $data['remaining-balance'];
}
```

The [send example][send_example] also has full working examples.

### Receiving A Message

Inbound messages are [sent to your application as a webhook][doc_inbound], and the client library provides a way to 
create an inbound message object from a webhook:

```php
$inbound = \Nexmo\Message\InboundMessage::createFromGlobals();
if($inbound->isValid()){
    error_log($inbound->getBody());
} else {
    error_log('invalid message');
}
```
    
You can also access the webhook data as an arry:

```php
$inbound = \Nexmo\Message\InboundMessage::createFromGlobals();
error_log($inbound['to']);
```

### Fetching A Message

You can retrieve a message log from the API using the ID of the message:

```php
$message = $client->message()->search('02000000DA7C52E7');
echo "The body of the message was: " . $message->getBody();
```

If the message was sent to a Nexmo virtual number, the object will be an instance of `Nexmo\Message\InboundMessage`, if 
the message was sent from your account, it will be an instance of `Nexmo\Message\Message`. You can also pass a message 
object to the client:

```php
$message = new \Nexmo\Message\InboundMessage('02000000DA7C52E7');
$client->message()->search($message);
echo "The body of the message was: " . $message->getBody();
```

### Starting a Verification

Nexmo's [Verify API][doc_verify] makes it easy to prove that a user has provided their own phone number during signup, 
or implement second factor authentication during signin.

You can start a verification process using a simple array:

```php
$verification = $client->verify()->start([
    'number' => '14845551212',
    'brand'  => 'My App'
]);
echo "Started verification with an id of: " . $verification->getRequestId();
```

Or you can pass the client a verification object:

```php
$verification = new \Nexmo\Verify\Verification('14845551212', 'My App');
$client->verify()->start($verification);
echo "Started verification with an id of: " . $verification->getRequestId();
```
    
### Controlling a Verification
    
To cancel an in-progress verification, or to trigger the next attempt to send the confirmation code, you can pass 
either an exsisting verification object to the client library, or simply use a request ID:

```php
$client->verify()->trigger('00e6c3377e5348cdaf567e1417c707a5');

$verification = new \Nexmo\Verify\Verification('00e6c3377e5348cdaf567e1417c707a5');
$client->verify()->cancel($verification);
```

### Checking A Verification

In the same way, checking a verification requires the code the user provided, and an exiting verification object:

```php
$verification = new \Nexmo\Verify\Verification('00e6c3377e5348cdaf567e1417c707a5');
$client->verify()->check($verification, '1234');
```
 
Or a request ID:

```php
$client->verify()->check('00e6c3377e5348cdaf567e1417c707a5', '1234');
```

### Searching For A Verification

You can check the status of a verification, or access the results of past verifications using either an exsisting 
verification object, or a request ID. The verification object will then provide a rich interface:

```php
$verification = new \Nexmo\Verify\Verification('00e6c3377e5348cdaf567e1417c707a5');
$client->verify()->search($verification);

echo "Codes checked for verification: " . $verification->getRequestId() . PHP_EOL;
foreach($verification->getChecks() as $check){
    echo $check->getDate()->format('d-m-y') . ' ' . $check->getStatus() . PHP_EOL;
}
```

You can also access the raw API response here using array access:

```php
$verification = new \Nexmo\Verify\Verification('00e6c3377e5348cdaf567e1417c707a5');
$client->verify()->search($verification);
echo "Verification cost was: " . $verification['price'] . PHP_EOL;
```

### Making A Call 

All `$client->calls()` methods require the client to be constructed with a `Nexmo\Client\Credentials\Keypair`, or a 
`Nexmo\Client\Credentials\Container` that includes the `Keypair` credentials:

```php
$basic  = new \Nexmo\Client\Credentials\Basic('key', 'secret');
$keypair = new \Nexmo\Client\Credentials\Keypair(file_get_contents(__DIR__ . '/application.key'), 'application_id');

$client = new \Nexmo\Client(new \Nexmo\Client\Credentials\Container($basic, $keypair));
```

You can start a call using an array as the structure:

```php
$client->calls()->create([
    'to' => [[
        'type' => 'phone',
        'number' => '14843331234'
    ]],
    'from' => [
        'type' => 'phone',
        'number' => '14843335555'
    ],
    'answer_url' => ['https://example.com/answer'],
    'event_url' => ['https://example.com/event'],
]);
```

Or you can create a `Nexmo\Calls\Call` object, and use that:

```php
use Nexmo\Calls\Call;
$call = new Call();
$call->setTo('14843331234')
     ->setFrom('14843335555')
     ->setWebhook(Call::WEBHOOK_ANSWER, 'https://example.com/answer')
     ->setWebhook(Call::WEBHOOK_EVENT, 'https://example.com/event');

$client->calls()->create($call);
```

### Fetching A Call

You can fetch a call using a `Nexmo\Calls\Call` object, or the call's UUID as a string:

```php
$call = $client->calls()->get('3fd4d839-493e-4485-b2a5-ace527aacff3');

$call = new Nexmo\Calls\Call('3fd4d839-493e-4485-b2a5-ace527aacff3');
$client->calls()->get($call);

echo $call->getDirection();
```

### Creating An Application

Application are configuration containers, and you can create one using a simple array structure:

```php
$application = $client->applications()->create([
    'name' => 'My Application',
    'answer_url' => 'https://example.com/answer',
    'event_url' => 'https://example.com/event'
])
```

You can also pass the client an application object:

```php
$application = new Nexmo\Application\Application();
$application->setName('My Application');
$application->getVoiceConfig()->setWebhook(VoiceConfig::ANSWER, 'https://example.com/answer');
$application->getVoiceConfig()->setWebhook(VoiceConfig::EVENT, 'https://example.com/event');

$client->appliations()->create($application);
```

### Fetching Applications

You can iterate over all your applications:

```php
foreach($client->applications() as $application){
    echo $application->getName() . PHP_EOL;
}
```

Or you can fetch an application using a string UUID, or an application object.

```php
$application = $client->applications()->get('1a20a124-1775-412b-b623-e6985f4aace0');

$application = new Application('1a20a124-1775-412b-b623-e6985f4aace0');
$client->applications()->get($application);
```

### Updating an Application

Once you have an application object, you can modify and save it. 

```php
$application = $client->applications()->get('1a20a124-1775-412b-b623-e6985f4aace0');

$application->setName('Updated Application');
$client->applications()->update($application);
```

You can also pass an array and the application UUID to the client:

```php
$application = $client->applications()->update([
    'name' => 'Updated Application',
    'answer_url' => 'https://example.com/v2/answer',
    'event_url' => 'https://example.com/v2/event'
], '1a20a124-1775-412b-b623-e6985f4aace0');
```


    
API Coverage
------------

* Account
    * [ ] Balance
    * [ ] Pricing
    * [ ] Settings
    * [ ] Top Up
    * [X] Numbers
        * [X] Search
        * [ ] Buy
        * [ ] Cancel
        * [X] Update
* Number Insight
    * [ ] Basic
    * [ ] Standard
    * [ ] Advanced
    * [ ] Webhook Notification
* Verify
    * [X] Verify
    * [X] Check
    * [X] Search
    * [X] Control
* Messaging 
    * [X] Send
    * [ ] Delivery Receipt
    * [X] Inbound Messages
    * [X] Search
        * [X] Message
        * [ ] Messages
        * [ ] Rejections
    * [ ] US Short Codes
        * [ ] Two-Factor Authentication
        * [ ] Event Based Alerts
            * [ ] Sending Alerts
            * [ ] Campaign Subscription Management
* Voice
    * [X] Outbound Calls
    * [ ] Inbound Call
    * [ ] Text-To-Speech Call
    * [ ] Text-To-Speech Prompt

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

[signup]: https://dashboard.nexmo.com/sign-up?utm_source=DEV_REL&utm_medium=github&utm_campaign=php-client-library
[doc_sms]: https://docs.nexmo.com/api-ref/sms-api?utm_source=DEV_REL&utm_medium=github&utm_campaign=php-client-library
[doc_inbound]: https://docs.nexmo.com/messaging/sms-api/api-reference#inbound?utm_source=DEV_REL&utm_medium=github&utm_campaign=php-client-library
[doc_verify]: https://docs.nexmo.com/verify/api-reference?utm_source=DEV_REL&utm_medium=github&utm_campaign=php-client-library
[license]: LICENSE.txt
[send_example]: examples/send.php
[spec]: https://github.com/Nexmo/client-library-specification
