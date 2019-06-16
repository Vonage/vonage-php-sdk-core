Client Library for PHP 
============================
[![Build Status](https://api.travis-ci.org/Nexmo/nexmo-php.svg?branch=master)](https://travis-ci.org/Nexmo/nexmo-php)
[![Latest Stable Version](https://poser.pugx.org/nexmo/client/v/stable)](https://packagist.org/packages/nexmo/client)
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE.txt)

*This library requires a minimum PHP version of 7.1*

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

To install the PHP client library to your project, we recommend using [Composer](https://getcomposer.org/).

```bash
composer require nexmo/client
```

> You don't need to clone this repository to use this library in your own projects. Use Composer to install it from Packagist.

If you're new to Composer, here are some resources that you may find useful:

* [Composer's Getting Started page](https://getcomposer.org/doc/00-intro.md) from Composer project's documentation.
* [A Beginner's Guide to Composer](https://scotch.io/tutorials/a-beginners-guide-to-composer) from the good people at ScotchBox.

Usage
-----

If you're using Composer, make sure the autoloader is included in your project's bootstrap file:

```php
require_once "vendor/autoload.php";
```

Create a client with your API key and secret:

```php
$client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET));     
```

For testing purposes you may want to change the URL that `nexmo-php` makes requests to from `api.nexmo.com` to something else. You can do this by providing an array containing `base_api_url` as the second parameter when creating a `Nexmo\Client` instance.

```php
$client = new Nexmo\Client(
    new Nexmo\Client\Credentials\Basic(API_KEY, API_SECRET),
    [
        'base_api_url' => 'https://example.com'
    ]
);

```

For APIs that would usually hit `rest.nexmo.com`, supplying a `base_rest_url` as an option to the constructor will change those requests.

Examples
--------

### Sending a Message

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

### Receiving a Message

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
    
You can also access the webhook data as an array:

```php
$inbound = \Nexmo\Message\InboundMessage::createFromGlobals();
error_log($inbound['to']);
```

### Fetching a Message

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

### Signing a Message

_You may also like to read the [documentation about message signing](https://developer.nexmo.com/concepts/guides/signing-messages)._

The SMS API supports the ability to sign messages by generating and adding a signature using a "Signature Secret" rather than your API secret.  The algorithms supported are:

* `md5hash1`
* `md5`
* `sha1`
* `sha256`
* `sha512`

Both your application and Nexmo need to agree on which algorithm is used. In the [dashboard](https://dashboard.nexmo.com), visit your account settings page and under "API Settings" you can select the algorithm to use. This is also the location where you will find your "Signature Secret" (it's different from the API secret).

Create a client using these credentials and the algorithm to use, for example:

```php
$client = new Nexmo\Client(new Nexmo\Client\Credentials\SignatureSecret(API_KEY, SIGNATURE_SECRET, 'sha256'));
```

Using this client, your SMS API messages will be sent as signed messages.

### Verifying an Incoming Message Signature

_You may also like to read the [documentation about message signing](https://developer.nexmo.com/concepts/guides/signing-messages)._

If you have message signing enabled for incoming messages, the SMS webhook will include the fields `sig`, `nonce` and `timestamp`. To verify the signature is from Nexmo, you create a Signature object using the incoming data, your signature secret and the signature method. Then use the `check()` method with the actual signature that was received (usually `_GET['sig']`) to make sure that it is correct.

```php
$signature = new \Nexmo\Client\Signature($_GET, SIGNATURE_SECRET, 'sha256');

// is it valid? Will be true or false
$isValid = $signature->check($_GET['sig']);
```

Using your signature secret and the other supplied parameters, the signature can be calculated and checked against the incoming signature value.

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
either an existing verification object to the client library, or simply use a request ID:

```php
$client->verify()->trigger('00e6c3377e5348cdaf567e1417c707a5');

$verification = new \Nexmo\Verify\Verification('00e6c3377e5348cdaf567e1417c707a5');
$client->verify()->cancel($verification);
```

### Checking a Verification

In the same way, checking a verification requires the code the user provided, and an existing verification object:

```php
$verification = new \Nexmo\Verify\Verification('00e6c3377e5348cdaf567e1417c707a5');
try {
    $client->verify()->check($verification, '1234');
    echo "Verification was successful (status: " . $verification['status'] . ")\n";
} catch (Exception $e) {
    $verification = $e->getEntity();
    echo "Verification failed with status " . $verification['status']
        . " and error text \"" . $verification['error_text'] . "\"\n";
}
```
 
Or a request ID:

```php
try {
    $verification = $client->verify()->check('00e6c3377e5348cdaf567e1417c707a5', '1234');
    echo "Verification was successful (status: " . $verification['status'] . ")\n";
} catch (Exception $e) {
    $verification = $e->getEntity();
    echo "Verification failed with status " . $verification['status']
        . " and error text \"" . $verification['error_text'] . "\"\n";
}
```

### Searching For a Verification

You can check the status of a verification, or access the results of past verifications using either an existing 
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

### Making a Call 

All `$client->calls()` methods require the client to be constructed with a `Nexmo\Client\Credentials\Keypair`, or a 
`Nexmo\Client\Credentials\Container` that includes the `Keypair` credentials:

```php
$basic  = new \Nexmo\Client\Credentials\Basic('key', 'secret');
$keypair = new \Nexmo\Client\Credentials\Keypair(
    file_get_contents((NEXMO_APPLICATION_PRIVATE_KEY_PATH),
    NEXMO_APPLICATION_ID
);

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

Or you can provide an NCCO directly in the POST request

```
$call = $client->calls()->create([
    'to' => [[
        'type' => 'phone',
        'number' => '14843331234'
    ]],
    'from' => [
        'type' => 'phone',
        'number' => '14843335555'
    ],
    'ncco' => [
        [
            'action' => 'talk',
            'text' => 'This is a text to speech call from Nexmo'
        ]
    ]
]);
```

Or you can create a `Nexmo\Call\Call` object, and use that:

```php
use Nexmo\Call\Call;
$call = new Call();
$call->setTo('14843331234')
     ->setFrom('14843335555')
     ->setWebhook(Call::WEBHOOK_ANSWER, 'https://example.com/answer')
     ->setWebhook(Call::WEBHOOK_EVENT, 'https://example.com/event');

$client->calls()->create($call);
```

The same example, providing an NCCO directly:

```php
use Nexmo\Call\Call;
$call = new Call();
$call->setTo('14843331234')
     ->setFrom('14843335555')
     ->setNcco([
        [
            'action' => 'talk',
            'text' => 'This is a text to speech call from Nexmo'
        ]
      ]);

$client->calls()->create($call);
```

### Fetching a Call

You can fetch a call using a `Nexmo\Call\Call` object, or the call's UUID as a string:

```php
$call = $client->calls()->get('3fd4d839-493e-4485-b2a5-ace527aacff3');

$call = new Nexmo\Call\Call('3fd4d839-493e-4485-b2a5-ace527aacff3');
$client->calls()->get($call);

echo $call->getDirection();
```

The call collection can also be treated as an array:
 
```php
echo $client->calls['3fd4d839-493e-4485-b2a5-ace527aacff3']->getDirection();
```

And iterated over:

```php
foreach($client->calls as $call){
    echo $call->getDirection();
}
```

With an optional filter:

```php
$filter = new \Nexmo\Call\Filter()->setStatus('completed');
foreach($client->calls($filter) as $call){
    echo $call->getDirection();
}
```

### Creating an Application

Application are configuration containers. You can create one using a simple array structure:

```php
$application = [
 'name' => 'test application',
 'keys' => [
     'public_key' => '-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCA\nKOxjsU4pf/sMFi9N0jqcSLcjxu33G\nd/vynKnlw9SENi+UZR44GdjGdmfm1\ntL1eA7IBh2HNnkYXnAwYzKJoa4eO3\n0kYWekeIZawIwe/g9faFgkev+1xsO\nOUNhPx2LhuLmgwWSRS4L5W851Xe3f\nUQIDAQAB\n-----END PUBLIC KEY-----\n'
 ],
 'capabilities' => [
     'voice' => [
         'webhooks' => [
             'answer_url' => [
                 'address' => 'https://example.com/answer',
                 'http_method' => 'GET',
             ],
             'event_url' => [
                 'address' => 'https://example.com/event',
                 'http_method' => 'POST',
             ],
         ]
     ],
     'messages' => [
         'webhooks' => [
             'inbound_url' => [
                 'address' => 'https://example.com/inbound',
                 'http_method' => 'POST'

             ],
             'status_url' => [
                 'address' => 'https://example.com/status',
                 'http_method' => 'POST'
             ]
         ]
     ],
     'rtc' => [
         'webhooks' => [
             'event_url' => [
                 'address' => 'https://example.com/event',
                 'http_method' => 'POST',
             ],
         ]
     ],
     'vbc' => []
 ]
];

$client->applications()->create($application);
```

You can also pass the client an application object:

```php
$a = new Nexmo\Application\Application;

$a->setName('PHP Client Example');
$a->getVoiceConfig()->setWebhook('answer_url', 'https://example.com/answer', 'GET');
$a->getVoiceConfig()->setWebhook('event_url', 'https://example.com/event', 'POST');
$a->getMessagesConfig()->setWebhook('status_url', 'https://example.com/status', 'POST');
$a->getMessagesConfig()->setWebhook('inbound_url', 'https://example.com/inbound', 'POST');
$a->getRtcConfig()->setWebhook('event_url', 'https://example.com/event', 'POST');
$a->disableVbc();

$client->applications()->create($a);
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
], '1a20a124-1775-412b-b623-e6985f4aace0');
```

### List Your Numbers

You can list the numbers owned by your account and optionally include filtering:

`search_pattern`:
* `0` - the number begins with `pattern`
* `1` - the number includes `pattern`
* `2` - the number ends with `pattern`

```
$client->numbers()->searchOwned(
    '234',
    [
        "search_pattern" => 1,
    ]
);
```

### Search Available Numbers

You can search for numbers available to purchase in a specific country:

```php
$numbers = $client->numbers()->searchAvailable('US');
```

### Purchase a Number

To purchase a number, you can pass in a value returned from number search:

```php
$numbers = $client->numbers()->searchAvailable('US');
$client->numbers()->purchase($numbers[0]);
```

Or you can specify the number and country manually:

```php
$client->numbers()->purchase('14155550100', 'US');
```

### Update a Number

To update a number, use `numbers()->update` and pass in the configuration options you want to change. To clear a setting, pass in an empty value.

```php
$client->numbers()->update([
    "messagesCallbackType" => "app",
    "messagesCallbackValue" => '1a20a124-1775-412b-b623-e6985f4aace0',
    "voiceCallbackType" => 'tel',
    "voiceCallbackValue" => '447700900002',
    "voiceStatusCallback" => 'https://example.com/webhooks/status',
    "moHttpUrl" => 'https://example.com/webhooks/inbound-sms',
], NEXMO_NUMBER);
```

### Cancel a Number

To cancel a number, provide the `msisdn`:

```php
$client->numbers()->cancel('447700900002');
```


### Managing Secrets

An API is provided to allow you to rotate your API secrets. You can create a new secret (up to a maximum of two secrets) and delete the existing one once all applications have been updated.

To get a list of the secrets:

```php
$secretCollection = $client->account()->listSecrets(API_KEY);

foreach($secretCollection['secrets'] as $secret) {
    echo "ID: " . $secret['id'] . " (created " . $secret['created_at'] .")\n";
}
```

You can create a new secret (the created dates will help you know which is which):

```php
$client->account()->createSecret(API_KEY, 'awes0meNewSekret!!;');
```

And delete the old secret (any application still using these credentials will stop working):

```php
try {
    $response = $client->account()->deleteSecret(API_KEY, 'd0f40c7e-91f2-4fe0-8bc6-8942587b622c');
} catch(\Nexmo\Client\Exception\Request $e) {
    echo $e->getMessage();
}
```
### Pricing

#### Prefix Pricing

If you know the prefix of a country that you want to call, you can use the `prefix-pricing` endpoint to
find out costs to call that number. Each prefix can return multiple countries (e.g. `1` returns `US`, `CA` and `UM`):

```
$results = $client->account()->getPrefixPricing('1');
foreach ($results as $r) {
    echo $r['country_code'].PHP_EOL;
    echo $r['country_name'].PHP_EOL;
    foreach ($r['networks'] as $n) {
        echo $n->getName() .' :: '.$n->getCode().' :: '.$n->getPrefixPrice().PHP_EOL;
    }
    echo "----------------".PHP_EOL;
}
```


### Check your Balance

Check how much credit remains on your account:

```php
$response = $client->account()->getBalance();
echo round($response->data['balance'], 2) . " EUR\n";
```

### View and Change Account Configuration

Inspect the current settings on the account:

```php
$response = $client->account()->getConfig();
print_r($response->data);
```

Update the default callback URLs for incoming SMS messages and delivery receipts:

```php
$response = $client->account()->updateConfig([
    "sms_callback_url" => "http://example.com/webhooks/incoming-sms",
    "dr_callback_url" => "http://example.com/webhooks/delivery-receipt"
]);
print_r($response->data);
```

## Troubleshooting


### `unable to get local issuer certificate`

Some users have issues making requests due to the following error:

```
Fatal error: Uncaught exception 'GuzzleHttp\Exception\RequestException' with message 'cURL error 60: SSL certificate problem: unable to get local issuer certificate (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)'
```

This is due to some PHP installations not shipping with a list of trusted CA certificates. This is a system configuration problem, and not specific to either cURL or Nexmo.

> *IMPORTANT*: In the next paragraph we provide a link to a CA certificate bundle. Nexmo do not guarantee the safety of this bundle, and you should review it yourself before installing any CA bundle on your machine.

To resolve this issue, download a list of trusted CA certificates (e.g. the [curl](https://curl.haxx.se/ca/cacert.pem) bundle) and copy it on to your machine. Once this is done, edit `php.ini` and set the `curl.cainfo` parameter:

```
# Linux/MacOS
curl.cainfo = "/etc/pki/tls/cacert.pem"
# Windows
curl.cainfo = "C:\php\extras\ssl\cacert.pem"
```

### Pass custom HTTP client

We allow use of any HTTPlug adapter, so you can create a client with alternative configuration if you need it, for example to take account of a local proxy, or deal with something else specific to your setup.

Here's an example that reduces the default timeout to 5 seconds to avoid long delays if you have no route to our servers:

```
$adapter_client = new Http\Adapter\Guzzle6\Client(new GuzzleHttp\Client(['timeout' => 5]));
$nexmo_client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic($api_key, $api_secret), [], $adapter_client);
```

### Composer installation fails due to Guzzle Adapter

If you have a conflicting package installation that cannot co-exist with our recommended `php-http/guzzle6-adapter` package, then you may install the package `nexmo/client-core` along with any package that satisfies the `php-http/client-implementation` requirement.

See the [Packagist page for client-implementation](https://packagist.org/providers/php-http/client-implementation) for options.

API Coverage
------------

* Account
    * [X] Balance
    * [X] Pricing
    * [ ] Settings
    * [X] Top Up
    * [X] Secret Management
    * [X] Pricing
    * [X] Numbers
        * [X] Search
        * [X] Buy
        * [X] Cancel
        * [X] Update
* Number Insight
    * [X] Basic
    * [X] Standard
    * [X] Advanced
    * [X] Webhook Notification
* Verify
    * [X] Verify
    * [X] Check
    * [X] Search
    * [X] Control
* Messaging 
    * [X] Send
    * [X] Delivery Receipt
    * [X] Inbound Messages
    * [X] Search
        * [X] Message
        * [X] Messages
        * [X] Rejections
    * [X] US Short Codes
        * [X] Two-Factor Authentication
        * [X] Event Based Alerts
            * [X] Sending Alerts
            * [X] Campaign Subscription Management
* Voice
    * [X] Outbound Call
    * [X] Outbound Call with an NCCO
    * [X] Inbound Call
    * [X] Text-To-Speech Call
    * [X] Text-To-Speech Prompt

Contributing
------------

This library is currently being refactored from an earlier prototype to match the current [client library spec][spec].
The `legacy` branch can be used to require that earlier version. During the transition the `develop` and `master` 
branches will have both new and legacy code. The [API coverage](#API-Coverage) section identifies what features are 
currently implemented and up to date. 

To contribute to the library, docs, or examples, [create an issue][issues] or [a pull request][pulls]. Please only raise issues
about features marked as working in the [API coverage](#API-Coverage) as the rest of the code is being updated.

License
-------

This library is released under the [MIT License][license]

[signup]: https://dashboard.nexmo.com/sign-up?utm_source=DEV_REL&utm_medium=github&utm_campaign=php-client-library
[doc_sms]: https://developer.nexmo.com/messaging/sms/overview
[doc_inbound]: https://developer.nexmo.com/messaging/sms/guides/inbound-sms
[doc_verify]: https://developer.nexmo.com/verify/overview
[license]: LICENSE.txt
[send_example]: examples/send.php
[spec]: https://github.com/Nexmo/client-library-specification
[issues]: https://github.com/Nexmo/nexmo-php/issues
[pulls]: https://github.com/Nexmo/nexmo-php/pulls

