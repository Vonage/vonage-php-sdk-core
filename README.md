Client Library for PHP 
============================
[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](CODE_OF_CONDUCT.md)
[![Build Status](https://github.com/vonage/vonage-php-sdk-core/workflows/build/badge.svg)](https://github.com/Vonage/vonage-php-sdk-core/actions?query=workflow%3Abuild)
[![Latest Stable Version](https://poser.pugx.org/vonage/client/v/stable)](https://packagist.org/packages/vonage/client)
[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE.txt)
[![codecov](https://codecov.io/gh/Vonage/vonage-php-sdk-core/branch/master/graph/badge.svg)](https://codecov.io/gh/vonage/vonage-php-sdk-core)

<img src="https://developer.nexmo.com/assets/images/Vonage_Nexmo.svg" height="48px" alt="Nexmo is now known as Vonage" />

*This library requires a minimum PHP version of 7.1*

This is the PHP client library for use Vonage's API. To use this, you'll need a Vonage account. Sign up [for free at 
nexmo.com][signup].

 * [Installation](#installation)
 * [Usage](#usage)
 * [Examples](#examples)
 * [Contributing](#contributing) 

Installation
------------

To use the client library you'll need to have [created a Vonage account][signup]. 

To install the PHP client library to your project, we recommend using [Composer](https://getcomposer.org/).

```bash
composer require vonage/client
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
$client = new Vonage\Client(new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));     
```

For testing purposes you may want to change the URL that `vonage/client` makes requests to from `api.nexmo.com` to something else. You can do this by providing an array containing `base_api_url` as the second parameter when creating a `Vonage\Client` instance.

```php
$client = new Vonage\Client(
    new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET),
    [
        'base_api_url' => 'https://example.com'
    ]
);

```

For APIs that would usually hit `rest.nexmo.com`, supplying a `base_rest_url` as an option to the constructor will change those requests.

Examples
--------

### Sending a Message

To use [Vonage's SMS API][doc_sms] to send an SMS message, call the `$client->sms()->send()` method.

**A message object** is is used to create the SMS messages. Each message type can be constructed with the 
required parameters, and a fluent interface provides access to optional parameters.

```php
$text = new \Vonage\SMS\Message\SMS(NEXMO_TO, NEXMO_FROM, 'Test message using PHP client library');
$text->setClientRef('test-message');
```

The message object is passed to the `send` method:

```php
$response = $client->sms()->send($text);
```
    
Once sent, the message object can be used to access the response data.

```php
$data = $response->current();
echo "Sent message to " . $data->getTo() . ". Balance is now " . $data->getRemainingBalance() . PHP_EOL;
```
    
Since each SMS message can be split into multiple messages, the response contains an object for each
message that was generated. You can check to see how many messages were generated using the standard
`count()` function in PHP. If you want to get the first message, you can use the `current()` method
on the response.

```php
$data = $response->current();
$data->getRemainingBalance();
foreach($response as $index => $data){
    $data->getRemainingBalance();
}
```

The [send example][send_example] also has full working examples.

### Receiving a Message

Inbound messages are [sent to your application as a webhook][doc_inbound], and the client library provides a way to 
create an inbound message object from a webhook:

```php
try {
    $inbound = \Vonage\SMS\InboundSMS::createFromGlobals();
    error_log($inbound->getText());
} catch (\InvalidArgumentException $e) {
    error_log('invalid message');
}
```
    
### Signing a Message

_You may also like to read the [documentation about message signing](https://developer.nexmo.com/concepts/guides/signing-messages)._

The SMS API supports the ability to sign messages by generating and adding a signature using a "Signature Secret" rather than your API secret.  The algorithms supported are:

* `md5hash1`
* `md5`
* `sha1`
* `sha256`
* `sha512`

Both your application and Vonage need to agree on which algorithm is used. In the [dashboard](https://dashboard.nexmo.com), visit your account settings page and under "API Settings" you can select the algorithm to use. This is also the location where you will find your "Signature Secret" (it's different from the API secret).

Create a client using these credentials and the algorithm to use, for example:

```php
$client = new Vonage\Client(new Vonage\Client\Credentials\SignatureSecret(API_KEY, SIGNATURE_SECRET, 'sha256'));
```

Using this client, your SMS API messages will be sent as signed messages.

### Verifying an Incoming Message Signature

_You may also like to read the [documentation about message signing](https://developer.nexmo.com/concepts/guides/signing-messages)._

If you have message signing enabled for incoming messages, the SMS webhook will include the fields `sig`, `nonce` and `timestamp`. To verify the signature is from Vonage, you create a Signature object using the incoming data, your signature secret and the signature method. Then use the `check()` method with the actual signature that was received (usually `_GET['sig']`) to make sure that it is correct.

```php
$signature = new \Vonage\Client\Signature($_GET, SIGNATURE_SECRET, 'sha256');

// is it valid? Will be true or false
$isValid = $signature->check($_GET['sig']);
```

Using your signature secret and the other supplied parameters, the signature can be calculated and checked against the incoming signature value.

### Starting a Verification

Vonage's [Verify API][doc_verify] makes it easy to prove that a user has provided their own phone number during signup,
or implement second factor authentication during signin.

You can start a verification process using code like this:

```php
$request = new \Vonage\Verify\Request('14845551212', 'My App');
$response = $client->verify()->start($request);
echo "Started verification with an id of: " . $response->getRequestId();
```

Once the user inputs the pin code they received, call the `/check` endpoint with the request ID and the pin to confirm the pin is correct.

### Controlling a Verification
    
To cancel an in-progress verification, or to trigger the next attempt to send the confirmation code, you can pass 
either an existing verification object to the client library, or simply use a request ID:

```php
$client->verify()->trigger('00e6c3377e5348cdaf567e1417c707a5');
$client->verify()->cancel('00e6c3377e5348cdaf567e1417c707a5');
```

### Checking a Verification

In the same way, checking a verification requires the code the user provided, and the request ID:

```php
try {
    $client->verify()->check('00e6c3377e5348cdaf567e1417c707a5', '1234');
    echo "Verification was successful (status: " . $verification->getStatus() . ")\n";
} catch (Exception $e) {
    echo "Verification failed with status " . $e->getCode()
        . " and error text \"" . $e->getMessage() . "\"\n";
}
```

### Searching For a Verification

You can check the status of a verification, or access the results of past verifications using a request ID. 
The verification object will then provide a rich interface:

```php
$client->verify()->search('00e6c3377e5348cdaf567e1417c707a5');

echo "Codes checked for verification: " . $verification->getRequestId() . PHP_EOL;
foreach($verification->getChecks() as $check){
    echo $check->getDate()->format('d-m-y') . ' ' . $check->getStatus() . PHP_EOL;
}
```

### Payment Verification

Vonage's [Verify API][doc_verify] has SCA (Secure Customer Authentication) support, required by the PSD2 (Payment Services Directive) and used by applications that need to get confirmation from customers for payments. It includes the payee and the amount in the message.

Start the verification for a payment like this:

```php
$request = new \Vonage\Verify\RequestPSD2('14845551212', 'My App');
$response = $client->verify()->requestPSD2($request);
echo "Started verification with an id of: " . $response['request_id'];
```

Once the user inputs the pin code they received, call the `/check` endpoint with the request ID and the pin to confirm the pin is correct.

### Making a Call 

All `$client->voice()` methods require the client to be constructed with a `Vonage\Client\Credentials\Keypair`, or a 
`Vonage\Client\Credentials\Container` that includes the `Keypair` credentials:

```php
$basic  = new \Vonage\Client\Credentials\Basic('key', 'secret');
$keypair = new \Vonage\Client\Credentials\Keypair(
    file_get_contents((NEXMO_APPLICATION_PRIVATE_KEY_PATH),
    NEXMO_APPLICATION_ID
);

$client = new \Vonage\Client(new \Vonage\Client\Credentials\Container($basic, $keypair));
```

You can start a call using an `OutboundCall` object:

```php
$outboundCall = new \Vonage\Voice\OutboundCall(
    new \Vonage\Voice\Endpoint\Phone('14843331234'),
    new \Vonage\Voice\Endpoint\Phone('14843335555')
);
$outboundCall
    ->setAnswerWebhook(
        new \Vonage\Voice\Webhook('https://example.com/answer')
    )
    ->setEventWebhook(
        new \Vonage\Voice\Webhook('https://example.com/event')
    )
;

$response = $client->voice()->createOutboundCall($outboundCall);
```

Or you can provide an NCCO directly in the POST request

```php
$outboundCall = new \Vonage\Voice\OutboundCall(
    new \Vonage\Voice\Endpoint\Phone('14843331234'),
    new \Vonage\Voice\Endpoint\Phone('14843335555')
);
$ncco = new NCCO();
$ncco->addAction(new \Vonage\Voice\NCCO\Action\Talk('This is a text to speech call from Vonage'));
$outboundCall->setNCCO($ncco);

$response = $client->voice()->createOutboundCall($outboundCall);
```

### Fetching a Call

You can fetch a call using a `Vonage\Call\Call` object, or the call's UUID as a string:

```php
$call = $client->voice()->get('3fd4d839-493e-4485-b2a5-ace527aacff3');

echo $call->getDirection();
```

You can also search for calls using a Filter.

```php
$filter = new \Vonage\Voice\Filter\VoiceFilter();
$filter->setStatus('completed');
foreach($client->search($filter) as $call){
    echo $call->getDirection();
}
```

### Creating an Application

Application are configuration containers. You can create one using a simple array structure:

```php
$application = new \Vonage\Application\Application();
$application->fromArray([
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
]);

$client->applications()->create($application);
```

You can also pass the client an application object:

```php
$a = new Vonage\Application\Application;

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
foreach($client->applications()->getAll() as $application){
    echo $application->getName() . PHP_EOL;
}
```

Or you can fetch an application using a string UUID, or an application object.

```php
$application = $client->applications()->get('1a20a124-1775-412b-b623-e6985f4aace0');
```

### Updating an Application

Once you have an application object, you can modify and save it. 

```php
$application = $client->applications()->get('1a20a124-1775-412b-b623-e6985f4aace0');

$application->setName('Updated Application');
$client->applications()->update($application);
```

### List Your Numbers

You can list the numbers owned by your account and optionally include filtering:

`search_pattern`:
* `0` - the number begins with `pattern`
* `1` - the number includes `pattern`
* `2` - the number ends with `pattern`

```php
$filter = new \Vonage\Numbers\Filter\OwnedNumbers();
$filter
    ->setPattern(234)
    ->setSearchPattern(\Vonage\Numbers\Filter\OwnedNumbers::SEARCH_PATTERN_CONTAINS)
;
$response = $client->numbers()->searchOwned($filter);
```

`has_application`:
* `true` - The number is attached to an application
* `false` - The number is not attached to an application

```php
$filter = new \Vonage\Numbers\Filter\OwnedNumbers();
$filter->setHasApplication(true);
$response = $client->numbers()->searchOwned($filter);
```

`application_id`:
* Supply an application ID to get all of the numbers associated with the requestion application

```php
$filter = new \Vonage\Numbers\Filter\OwnedNumbers();
$filter->setApplicationId("66c04cea-68b2-45e4-9061-3fd847d627b8");
$response = $client->numbers()->searchOwned($filter);
```

### Search Available Numbers

You can search for numbers available to purchase in a specific country:

```php
$numbers = $client->numbers()->searchAvailable('US');
```

By default, this will only return the first 10 results. You can add an additional `\Vonage\Numbers\Filter\AvailableNumbers`
filter to narrow down your search.

### Purchase a Number

To purchase a number, you can pass in a value returned from number search:

```php
$numbers = $client->numbers()->searchAvailable('US');
$number = $numbers->current();
$client->numbers()->purchase($number->getMsisdn(), $number->getCountry());
```

Or you can specify the number and country manually:

```php
$client->numbers()->purchase('14155550100', 'US');
```

### Update a Number

To update a number, use `numbers()->update` and pass in the configuration options you want to change. To clear a setting, pass in an empty value.

```php
$number = $client->numbers()->get(NEXMO_NUMBER);
$number
    ->setAppId('1a20a124-1775-412b-b623-e6985f4aace0')
    ->setVoiceDestination('447700900002', 'tel')
    ->setWebhook(
        \Vonage\Number\Number::WEBHOOK_VOICE_STATUS,
        'https://example.com/webhooks/status'
    )
    ->setWebhook(
        \Vonage\Number\Number::WEBHOOK_MESSAGE,
        'https://example.com/webhooks/inbound-sms'
    )
;
$client->numbers()->update($number);
echo "Number updated" . PHP_EOL;
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
$secretsCollection = $client->account()->listSecrets(API_KEY);
/** @var \Vonage\Account\Secret $secret */
foreach($secretsCollection->getSecrets() as $secret) {
    echo "ID: " . $secret->getId() . " (created " . $secret->getCreatedAt() .")\n";
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
} catch(\Vonage\Client\Exception\Request $e) {
    echo $e->getMessage();
}
```
### Pricing

#### Prefix Pricing

If you know the prefix of a country that you want to call, you can use the `prefix-pricing` endpoint to
find out costs to call that number. Each prefix can return multiple countries (e.g. `1` returns `US`, `CA` and `UM`):

```php
$results = $client->account()->getPrefixPricing('1');
foreach ($results as $price) {
    echo $price->getCountryCode().PHP_EOL;
    echo $price->getCountryName().PHP_EOL;
    foreach ($price->getNetworks() as $network) {
        echo $network->getName() .' :: '.$network->getCode().' :: '.$network->getPrefixPrice().PHP_EOL;
    }
    echo "----------------".PHP_EOL;
}
```


### Check your Balance

Check how much credit remains on your account:

```php
$response = $client->account()->getBalance();
echo round($response->getBalance(), 2) . " EUR\n";
```

### View and Change Account Configuration

Inspect the current settings on the account:

```php
$response = $client->account()->getConfig();
print_r($response->toArray());
```

Update the default callback URLs for incoming SMS messages and delivery receipts:

```php
$response = $client->account()->updateConfig([
    "sms_callback_url" => "http://example.com/webhooks/incoming-sms",
    "dr_callback_url" => "http://example.com/webhooks/delivery-receipt"
]);
print_r($response->toArray());
```

### Get Information About a Number

The [Number Insights API](https://developer.nexmo.com/api/number-insight) allows a user to check that a number is valid and to find out more about how to use it.

#### Basic and Standard Usage

You can use either the `basic()` or `standard()` methods (an `advanced()` method is available, but it is recommended to use the async option to get advanced info), like this:

```php

try {
  $insights = $client->insights()->basic(PHONE_NUMBER);

  echo $insights->getNationalFormatNumber();
} catch (Exception $e) {
  // for the Vonage-specific exceptions, try the `getEntity()` method for more diagnostic information
}
```

The data is returned in the `$insights` variable in the example above.

#### Advanced Usage

To get advanced insights, use the async feature and supply a URL for the webhook to be sent to:

```php
try {
  $client->insights()->advancedAsync(PHONE_NUMBER, 'http://example.com/webhooks/number-insights');
} catch (Exception $e) {
  // for the Vonage-specific exceptions, try the `getEntity()` method for more diagnostic information
}
```

Check out the [documentation](https://developer.nexmo.com/number-insight/code-snippets/number-insight-advanced-async-callback) for what to expect in the incoming webhook containing the data you requested.

## Supported APIs

| API   | API Release Status |  Supported?
|----------|:---------:|:-------------:|
| Account API | General Availability |✅|
| Alerts API | General Availability |✅|
| Application API | General Availability |✅|
| Audit API | Beta |❌|
| Conversation API | Beta |❌|
| Dispatch API | Beta |❌|
| External Accounts API | Beta |❌|
| Media API | Beta | ❌|
| Messages API | Beta |❌|
| Number Insight API | General Availability |✅|
| Number Management API | General Availability |✅|
| Pricing API | General Availability |✅|
| Redact API | General Availability |✅|
| Reports API | Beta |❌|
| SMS API | General Availability |✅|
| Verify API | General Availability |✅|
| Voice API | General Availability |✅|

## Troubleshooting

### Checking for Deprecated Features

Over time, the Vonage APIs evolve and add new features, change how existing 
features work, and deprecate and remove older methods and features. To help
developers know when deprecation changes are being made, the SDK will trigger
an `E_USER_DEPRECATION` warning. These warnings will not stop the exectution
of code, but can be an annoyance in production environments.

To help with this, by default these notices are supressed. In development,
you can enable these warnings by passing an additional configuration option
to the `\Vonage\Client` constructor, called `show_deprecations`. Enabling this
option will show all deprecation notices.

```php
$client = new Vonage\Client(
    new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET),
    [
        'show_deprecations' => true
    ]
);
```

If you notice an excessive amount of deprecation notices in production
environments, make sure that this configuration option is absent, or at least
set to `false`.

### `unable to get local issuer certificate`

Some users have issues making requests due to the following error:

```
Fatal error: Uncaught exception 'GuzzleHttp\Exception\RequestException' with message 'cURL error 60: SSL certificate problem: unable to get local issuer certificate (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)'
```

This is due to some PHP installations not shipping with a list of trusted CA certificates. This is a system configuration problem, and not specific to either cURL or Vonage.

> *IMPORTANT*: In the next paragraph we provide a link to a CA certificate bundle. Vonage do not guarantee the safety of this bundle, and you should review it yourself before installing any CA bundle on your machine.

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

```php
$adapter_client = new Http\Adapter\Guzzle6\Client(new GuzzleHttp\Client(['timeout' => 5]));
$vonage_client = new Vonage\Client(new Vonage\Client\Credentials\Basic($api_key, $api_secret), [], $adapter_client);
```

### Accessing Response Data

When things go wrong, you'll receive an `Exception`. The Vonage exception classes `Vonage\Client\Exception\Request` and `Vonage\Client\Exception\Server` support an additional `getEntity()` method which you can use in addition to `getCode()` and `getMessage()` to find out more about what went wrong. The entity returned will typically be an object related to the operation, or the response object from the API call.

### Composer installation fails due to Guzzle Adapter

If you have a conflicting package installation that cannot co-exist with our recommended `php-http/guzzle6-adapter` package, then you may install the package `vonage/client-core` along with any package that satisfies the `php-http/client-implementation` requirement.

See the [Packagist page for client-implementation](https://packagist.org/providers/php-http/client-implementation) for options.

Contributing
------------

This library is actively developed and we love to hear from you! Please feel free to [create an issue][issues] or [open a pull request][pulls] with your questions, comments, suggestions and feedback.

[signup]: https://dashboard.nexmo.com/sign-up?utm_source=DEV_REL&utm_medium=github&utm_campaign=php-client-library
[doc_sms]: https://developer.nexmo.com/messaging/sms/overview
[doc_inbound]: https://developer.nexmo.com/messaging/sms/guides/inbound-sms
[doc_verify]: https://developer.nexmo.com/verify/overview
[license]: LICENSE.txt
[send_example]: examples/send.php
[spec]: https://github.com/Nexmo/client-library-specification
[issues]: https://github.com/Vonage/vonage-php-core/issues
[pulls]: https://github.com/Vonage/vonage-php-core/pulls

