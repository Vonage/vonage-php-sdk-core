Client Library for PHP 
============================
[![Contributor Covenant](https://img.shields.io/badge/Contributor%20Covenant-v2.0%20adopted-ff69b4.svg)](CODE_OF_CONDUCT.md)
[![Build Status](https://github.com/vonage/vonage-php-sdk-core/workflows/build/badge.svg)](https://github.com/Vonage/vonage-php-sdk-core/actions?query=workflow%3Abuild)
[![Latest Stable Version](https://poser.pugx.org/vonage/client/v/stable)](https://packagist.org/packages/vonage/client)
[![License](https://img.shields.io/badge/License-Apache_2.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![codecov](https://codecov.io/gh/Vonage/vonage-php-sdk-core/branch/master/graph/badge.svg)](https://codecov.io/gh/vonage/vonage-php-sdk-core)

![The Vonage logo](./vonage_logo.png)

*This library requires a minimum PHP version of 8.0*

This is the PHP client library for use Vonage's API. To use this, you'll need a Vonage account. [Sign up for free here](https://ui.idp.vonage.com/ui/auth/registration).

 * [Installation](#installation)
 * [Usage](#usage)
 * [Examples](#examples)
 * [Test Suite](#test-suite)
 * [Contributing](#contributing) 

Installation
------------

To use the client library you'll need to have [created a Vonage account][signup]. 

To install the PHP client library to your project, we recommend using [Composer](https://getcomposer.org/).

```bash
composer require vonage/client
```

> Note that this actually points to a wrapper library that includes an HTTP client -and- this core library. You can
> install this library directly from Composer if you wish, with the ability to choose the HTTP client your project
> uses.

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

For testing purposes you may want to change the URL that `vonage/client` makes requests to from `api.vonage.com` to something else. You can do this by providing an array containing `base_api_url` as the second parameter when creating a `Vonage\Client` instance.

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

### Sending a Message via the SMS API

To use [Vonage's SMS API][doc_sms] to send an SMS message, call the `$client->sms()->send()` method.

**A message object** is used to create the SMS messages. Each message type can be constructed with the 
required parameters, and a fluent interface provides access to optional parameters.

```php
$text = new \Vonage\SMS\Message\SMS(VONAGE_TO, VONAGE_FROM, 'Test message using PHP client library');
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

### Detecting Encoding Type

You can use a static `isGsm7()` method within the SMS Client code to determine whether to send the message using
GSM-7 encoding or Unicode. Here is an example:

```php
$sms = new \Vonage\SMS\Message\SMS('123', '456', 'is this gsm7?');

if (Vonage\SMS\Message\SMS::isGsm7($text)) {
    $sms->setType('text');
} else {
    $sms->setType('unicode');
}
```

### Receiving a Message

Inbound messages are [sent to your application as a webhook][doc_inbound]. The Client library provides a way to 
create an inbound message object from a webhook:

```php
try {
    $inbound = \Vonage\SMS\Webhook\Factory::createFromGlobals();
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

Create a client using these credentials, and the algorithm to use, for example:

```php
$client = new Vonage\Client(new Vonage\Client\Credentials\SignatureSecret(API_KEY, SIGNATURE_SECRET, 'sha256'));
```

Using this client, your SMS API messages will be sent as signed messages.

### Verifying an Incoming Message Signature

_You may also like to read the [documentation about message signing](https://developer.nexmo.com/concepts/guides/signing-messages)._

If you have message signing enabled for incoming messages, the SMS webhook will include the fields `sig`, `nonce` and `timestamp`. To verify the signature is from Vonage, you create a Signature object using the incoming data, your signature secret and the signature method. Then use the `check()` method with the actual signature that was received (usually `_GET['sig']`) to make sure, that it is correct.

```php
$signature = new \Vonage\Client\Signature($_GET, SIGNATURE_SECRET, 'sha256');

// is it valid? Will be true or false
$isValid = $signature->check($_GET['sig']);
```

Using your signature secret and the other supplied parameters, the signature can be calculated and checked against the incoming signature value.

### Sending a Message via. the Messages API

The [Messages API](https://developer.vonage.com/api/messages-olympus) is used to send a variety of outbound messages. 
The following platforms are currently supported:
* **SMS**
* **MMS**
* **WhatsApp**
* **Messenger**
* **Viber**

Each one of these platforms has a different category of message you can send (for example, with WhatsApp you can send
text, an image, audio, video, a file or a template but for Viber you can only send a text or an image). You can find
all the sendable message types under the namespace `\Vonage\Messages\Channel`. The reason each type is separated
out this way is that the platform and message type requires different parameters in the API call.

The `\Vonage\Messages\Client` is configured in a similar way to the SMS API Client. The difference is that the
authentication can be either a JSON Web Token (JWT) or Basic Authentication. You can find more info on how to set
up your Client's credentials under the 'Usage' section of this ReadMe.

Here some examples:

### Sending a WhatsApp Text

First, we need to create a new WhatsAppText object like so:

```php
$whatsAppText = new Vonage\Messages\Channel\WhatsApp\WhatsAppText(
    FROM_NUMBER,
    TO_NUMBER,
    'this is a WA text from vonage'
);
```

The Messages API Client has one method, `send()` where you can send any of the message types provided. So, to send this
message, the following code will do that, assuming you have already set up your Vonage client correctly:

```php
$client->messages()->send($whatsAppText);
```

Your response will be a JSON payload if the error range is with 200, or will throw a relevant `APIException` if it's
within 400/500.

### Send a Viber Image

Some `Channel` objects require more arguments in order to be created. You can see the rough mapping of these
requirements by comparing the constructor arguments vs. the API Documentation. Some of these messages take custom
reusable objects (that are under the `\Vonage\Messages\MessageObjects` namespace). One of these is an image - so
here is an example of how to send a Viber Image:

```php
$imageObject = Vonage\Messages\MessageObjects\ImageObject(
    'https://picsum.photos/200/300',
    'image caption'
);

$viberImage = new Vonage\Messages\Channel\Viber\ViberImage(
    FROM_NUMBER,
    TO_NUMBER,
    $imageObject
);

$client->messages()->send($viberImage);
```

### Verify Examples (v1)

#### Starting a Verification

Vonage's [Verify API][doc_verify] makes it easy to prove that a user has provided their own phone number during signup,
or implement second factor authentication during sign in.

You can start a verification process using code like this:

```php
$request = new \Vonage\Verify\Request('14845551212', 'My App');
$response = $client->verify()->start($request);
echo "Started verification with an id of: " . $response->getRequestId();
```

Once the user inputs the pin code they received, call the `check()` method (see below) with the request ID and the PIN to confirm the PIN is correct.

#### Controlling a Verification
    
To cancel an in-progress verification, or to trigger the next attempt to send the confirmation code, you can pass 
either an existing verification object to the client library, or simply use a request ID:

```php
$client->verify()->trigger('00e6c3377e5348cdaf567e1417c707a5');
$client->verify()->cancel('00e6c3377e5348cdaf567e1417c707a5');
```

#### Checking a Verification

In the same way, checking a verification requires the PIN the user provided, and the request ID:

```php
try {
    $client->verify()->check('00e6c3377e5348cdaf567e1417c707a5', '1234');
    echo "Verification was successful (status: " . $verification->getStatus() . ")\n";
} catch (Exception $e) {
    echo "Verification failed with status " . $e->getCode()
        . " and error text \"" . $e->getMessage() . "\"\n";
}
```

#### Searching For a Verification

You can check the status of a verification, or access the results of past verifications using a request ID. 
The verification object will then provide a rich interface:

```php
$client->verify()->search('00e6c3377e5348cdaf567e1417c707a5');

echo "Codes checked for verification: " . $verification->getRequestId() . PHP_EOL;
foreach($verification->getChecks() as $check){
    echo $check->getDate()->format('d-m-y') . ' ' . $check->getStatus() . PHP_EOL;
}
```

#### Payment Verification

Vonage's [Verify API][doc_verify] has SCA (Secure Customer Authentication) support, required by the PSD2 (Payment Services Directive) and used by applications that need to get confirmation from customers for payments. It includes the payee and the amount in the message.

Start the verification for a payment like this:

```php
$request = new \Vonage\Verify\RequestPSD2('14845551212', 'My App');
$response = $client->verify()->requestPSD2($request);
echo "Started verification with an id of: " . $response['request_id'];
```

Once the user inputs the pin code they received, call the `/check` endpoint with the request ID and the pin to confirm the pin is correct.

### Verify Examples (v2)

#### Starting a Verification

Vonage's Verify v2 relies more on asynchronous workflows via. webhooks, and more customisable Verification
workflows to the developer. To start a verification, you'll need the API client, which is under the namespace
`verify2`.

Making a Verify request needs a 'base' channel of communication to deliver the mode of verification. You can
customise these interactions by adding different 'workflows'. For each type of workflow, there is a Verify2 class
you can create that will handle the initial workflow for you. For example:

```php
$client = new Vonage\Client(
    new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET),
);

$smsRequest = new \Vonage\Verify2\Request\SMSRequest('TO_NUMBER');
$client->verify2()->startVerification($smsRequest);
```

The `SMSRequest` object will resolve defaults for you, and will create a default `workflow` object to use SMS.
You can, however, add multiple workflows that operate with fall-back logic. For example, if you wanted to create
a Verification that tries to get a PIN code off the user via. SMS, but in case there is a problem with SMS delivery
you wish to add a Voice fallback: you can add it.

```php
$client = new Vonage\Client(
    new Vonage\Client\Credentials\Basic(API_KEY, API_SECRET),
);

$smsRequest = new \Vonage\Verify2\Request\SMSRequest('TO_NUMBER', 'my-verification');
$voiceWorkflow = new \Vonage\Verify2\VerifyObjects\VerificationWorkflow(\Vonage\Verify2\VerifyObjects\VerificationWorkflow::WORKFLOW_VOICE, 'TO_NUMBER');
$smsRequest->addWorkflow($voiceWorkflow);
$client->verify2()->startVerification($smsRequest);
```
This adds the voice workflow to the original SMS request. The verification request will try and resolve the process in
the order that it is given (starting with the default for the type of request).

The base request types are as follows:

* `SMSRequest`
* `WhatsAppRequest`
* `WhatsAppInterativeRequest`
* `EmailRequest`
* `VoiceRequest`
* `SilentAuthRequest`

For adding workflows, you can see the available valid workflows as constants within the `VerificationWorkflow` object.
For a better developer experience, you can't create an invalid workflow due to the validation that happens on the object.

#### Check a submitted code

To submit a code, you'll need to surround the method in a try/catch due to the nature of the API. If the code is correct,
the method will return a `true` boolean. If it fails, it will throw the relevant Exception from the API that will need to
be caught.

```php
$code = '1234';
try {
    $client->verify2()->check($code);
} catch (\Exception $e) {
    var_dump($e->getMessage())
}
```

#### Webhooks

As events happen during a verification workflow, events and updates will fired as webhooks. Incoming server requests that conform to
PSR-7 standards can be hydrated into a webhook value object for nicer interactions. You can also hydrate
them from a raw array. If successful, you will receive a value object back for the type of event/update. Possible webhooks are:

* `VerifyEvent`
* `VerifyStatusUpdate`
* `VerifySilentAuthUpdate`

```php
// From a request object
$verificationEvent = \Vonage\Verify2\Webhook\Factory::createFromRequest($request);
var_dump($verificationEvent->getStatus());
// From an array
$payload = $request->getBody()->getContents()
$verificationEvent = \Vonage\Verify2\Webhook\Factory::createFromArray($payload);
var_dump($verificationEvent->getStatus());
```

#### Cancelling a request in-flight

You can cancel a request should you need to, before the end user has taken any action.

```php
$requestId = 'c11236f4-00bf-4b89-84ba-88b25df97315';
$client->verify2()->cancel($requestId);
```

#### Making a Call 

All `$client->voice()` methods require the client to be constructed with a `Vonage\Client\Credentials\Keypair`, or a 
`Vonage\Client\Credentials\Container` that includes the `Keypair` credentials:

```php
$basic  = new \Vonage\Client\Credentials\Basic(VONAGE_API_KEY, VONAGE_API_SECRET);
$keypair = new \Vonage\Client\Credentials\Keypair(
    file_get_contents(VONAGE_APPLICATION_PRIVATE_KEY_PATH),
    VONAGE_APPLICATION_ID
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
        new \Vonage\Voice\Webhook('https://example.com/webhooks/answer')
    )
    ->setEventWebhook(
        new \Vonage\Voice\Webhook('https://example.com/webhooks/event')
    )
;

$response = $client->voice()->createOutboundCall($outboundCall);
```

If you would like to have the system randomly pick a FROM number from the numbers linked to an application, you can
leave off the second parameter to `\Vonage\Voice\OutboundCall`'s constructor, and the system will select a number
at random for you.

### Building a call with NCCO Actions

Full parameter lists for NCCO Actions can be found in the [Voice API Docs](https://developer.nexmo.com/voice/voice-api/ncco-reference).

Each of these examples uses the following structure to add actions to a call:

```php
$outboundCall = new \Vonage\Voice\OutboundCall(
    new \Vonage\Voice\Endpoint\Phone('14843331234'),
    new \Vonage\Voice\Endpoint\Phone('14843335555')
);
$ncco = new NCCO();

// ADD ACTIONS TO THE NCCO OBJECT HERE

$outboundCall->setNCCO($ncco);

$response = $client->voice()->createOutboundCall($outboundCall);
```

### Record a call

```php
$outboundCall = new \Vonage\Voice\OutboundCall(
    new \Vonage\Voice\Endpoint\Phone('14843331234'),
    new \Vonage\Voice\Endpoint\Phone('14843335555')
);

$ncco = new NCCO();
$ncco->addAction(\Vonage\Voice\NCCO\Action\Record::factory([
    'eventUrl' => 'https://example.com/webhooks/event'
]);
$outboundCall->setNCCO($ncco);

$response = $client->voice()->createOutboundCall($outboundCall);
```

Your webhook url will receive a payload like this:

```
{
  "start_time": "2020-10-29T14:30:24Z",
  "recording_url": "https://api.nexmo.com/v1/files/<recording-id>",
  "size": 27918,
  "recording_uuid": "<recording-id>",
  "end_time": "2020-10-29T14:30:31Z",
  "conversation_uuid": "<conversation-id>",
  "timestamp": "2020-10-29T14:30:31.619Z"
}
```

You can then fetch and store the recording like this:

```
$recordingId = '<recording-id>';
$recordingUrl = 'https://api.nexmo.com/v1/files/' . $recordingId;
$data = $client->get($recordingUrl);
file_put_contents($recordingId.'.mp3', $data->getBody());
```

### Send a text to voice call
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

### Stream an audio file on a call
```php
$outboundCall = new \Vonage\Voice\OutboundCall(
    new \Vonage\Voice\Endpoint\Phone('14843331234'),
    new \Vonage\Voice\Endpoint\Phone('14843335555')
);

$ncco = new NCCO();
$ncco->addAction(new \Vonage\Voice\NCCO\Action\Stream('https://example.com/sounds/my-audio.mp3'));
$outboundCall->setNCCO($ncco);

$response = $client->voice()->createOutboundCall($outboundCall);
```

### Collect user input from a call

Supports keypad entry as well as voice. NB. the input action must follow an action with `bargeIn` set to `true`

```php
$outboundCall = new \Vonage\Voice\OutboundCall(
    new \Vonage\Voice\Endpoint\Phone('14843331234'),
    new \Vonage\Voice\Endpoint\Phone('14843335555')
);

$ncco = new NCCO();

$ncco->addAction(\Vonage\Voice\NCCO\Action\Talk::factory('Please record your name.',[
  'bargeIn' => true,
]));

$ncco->addAction(\Vonage\Voice\NCCO\Action\Input::factory([
  'eventUrl' => 'https://example.com/webhooks/event',
  'type' => [
    'speech',
  ],
  'speech' => [
    'endOnSilence' => true,
  ],
]));

$outboundCall->setNCCO($ncco);

$response = $client->voice()->createOutboundCall($outboundCall);
```

The webhook URL will receive a payload containing the input from the user with relative confidence ratings for speech input.

### Send a notification to a webhook url

```php
$outboundCall = new \Vonage\Voice\OutboundCall(
    new \Vonage\Voice\Endpoint\Phone('14843331234'),
    new \Vonage\Voice\Endpoint\Phone('14843335555')
);

$ncco = new NCCO();    
$ncco->addAction(new \Vonage\Voice\NCCO\Action\Talk('We are just testing the notify function, you do not need to do anything.'));
$ncco->addAction(new \Vonage\Voice\NCCO\Action\Notify([
  'foo' => 'bar',
], new Vonage\Voice\Webhook('https://example.com/webhooks/notify')));
$outboundCall->setNCCO($ncco);

$response = $client->voice()->createOutboundCall($outboundCall);
```
The webhook URL will receive a payload as specified in the request.

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

Application are configuration containers. You can create one using an array structure:

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
                 'address' => 'https://example.com/webhooks/answer',
                 'http_method' => 'GET',
             ],
             'event_url' => [
                 'address' => 'https://example.com/webhooks/event',
                 'http_method' => 'POST',
             ],
         ]
     ],
     'messages' => [
         'webhooks' => [
             'inbound_url' => [
                 'address' => 'https://example.com/webhooks/inbound',
                 'http_method' => 'POST'

             ],
             'status_url' => [
                 'address' => 'https://example.com/webhooks/status',
                 'http_method' => 'POST'
             ]
         ]
     ],
     'rtc' => [
         'webhooks' => [
             'event_url' => [
                 'address' => 'https://example.com/webhooks/event',
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
$a = new Vonage\Application\Application();

$a->setName('PHP Client Example');
$a->getVoiceConfig()->setWebhook('answer_url', 'https://example.com/webhooks/answer', 'GET');
$a->getVoiceConfig()->setWebhook('event_url', 'https://example.com/webhooks/event', 'POST');
$a->getMessagesConfig()->setWebhook('status_url', 'https://example.com/webhooks/status', 'POST');
$a->getMessagesConfig()->setWebhook('inbound_url', 'https://example.com/webhooks/inbound', 'POST');
$a->getRtcConfig()->setWebhook('event_url', 'https://example.com/webhooks/event', 'POST');
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
* Supply an application ID to get all the numbers associated with the requesting application

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
/** @var Vonage\Numbers\Number[] $numbers */
$numbers = $client->numbers()->searchAvailable('US');
$number = array_shift($numbers);
$client->numbers()->purchase($number->getMsisdn(), $number->getCountry());
```

Or you can specify the number and country manually:

```php
$client->numbers()->purchase('14155550100', 'US');
```

### Update a Number

To update a number, use `numbers()->update` and pass in the configuration options you want to change. To clear a setting, pass in an empty value.

```php
$number = $client->numbers()->get(VONAGE_NUMBER);
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

If you know the prefix of a country you want to call, you can use the `prefix-pricing` endpoint to
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

### Subaccount Examples

This API is used to create and configure subaccounts related to your primary account and transfer credit, balances and bought numbers between accounts.
The subaccounts API is disabled by default. If you want to use subaccounts, [contact support](https://api.support.vonage.com) to have the API enabled on your account.

#### Get a list of Subaccounts

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));
$apiKey = '34kokdf';
$subaccounts = $client->subaccount()->getSubaccounts($apiKey);
var_dump($subaccounts);
```

#### Create a Subaccount

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

$apiKey = 'acc6111f';

$payload = [
    'name' => 'sub name',
    'secret' => 's5r3fds',
    'use_primary_account_balance' => false
];

$account = new Account();
$account->fromArray($payload);

$response = $client->subaccount()->createSubaccount($apiKey, $account);
var_dump($response);
```

#### Get a Subaccount

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

$apiKey = 'acc6111f';
$subaccountKey = 'bbe6222f';

$response = $client->subaccount()->getSubaccount($apiKey, $subaccountKey);
var_dump($response);
```

#### Update a Subaccount

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

$apiKey = 'acc6111f';
$subaccountKey = 'bbe6222f';

$payload = [
    'suspended' => true,
    'use_primary_account_balance' => false,
    'name' => 'Subaccount department B'
];

$account = new Account();
$account->fromArray($payload);

$response = $client->subaccount()->updateSubaccount($apiKey, $subaccountKey, $account)
var_dump($response);
```

#### Get a list of Credit Transfers

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

$apiKey = 'acc6111f';
$filter = new Vonage\Subaccount\Filter\Subaccount(['subaccount' => '35wsf5'])
$transfers = $client->subaccount()->getCreditTransfers($apiKey);
var_dump($transfers);
```

#### Transfer Credit between accounts

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

$apiKey = 'acc6111f';

$transferRequest = (new TransferCreditRequest($apiKey))
    ->setFrom('acc6111f')
    ->setTo('s5r3fds')
    ->setAmount('123.45')
    ->setReference('this is a credit transfer');

$response = $this->subaccountClient->makeCreditTransfer($transferRequest);
```

#### Get a list of Balance Transfers

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));
$apiKey = 'acc6111f';

$filter = new \Vonage\Subaccount\Filter\Subaccount(['end_date' => '2022-10-02']);
$transfers = $client->subaccount()->getBalanceTransfers($apiKey, $filter);
```

#### Transfer Balance between accounts

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));

$apiKey = 'acc6111f';

$transferRequest = (new TransferBalanceRequest($apiKey))
    ->setFrom('acc6111f')
    ->setTo('s5r3fds')
    ->setAmount('123.45')
    ->setReference('this is a credit transfer');

$response = $client->subaccount()->makeBalanceTransfer($transferRequest);
var_dump($response);
```

#### Transfer a Phone Number between accounts

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(API_KEY, API_SECRET));
$apiKey = 'acc6111f';

$numberTransferRequest = (new NumberTransferRequest($apiKey))
    ->setFrom('acc6111f')
    ->setTo('s5r3fds')
    ->setNumber('4477705478484')
    ->setCountry('GB');

$response = $client->subaccount()->makeNumberTransfer($numberTransferRequest);
var_dump($response);
```

## Supported APIs

| API                    |  API Release Status  | Supported? 
|------------------------|:--------------------:|:----------:|
| Account API            | General Availability |     ✅      |
| Alerts API             | General Availability |     ✅      |
| Application API        | General Availability |     ✅      |
| Audit API              |         Beta         |     ❌      |
| Conversation API       |         Beta         |     ❌      |
| Dispatch API           |         Beta         |     ❌      |
| External Accounts API  |         Beta         |     ❌      |
| Media API              |         Beta         |     ❌      |
| Meetings API           | General Availability |     ✅      |
| Messages API           | General Availability |     ✅      |
| Number Insight API     | General Availability |     ✅      |
| Number Management API  | General Availability |     ✅      |
| Pricing API            | General Availability |     ✅      |
| ProActive Connect API  |         Beta         |     ❌      |
| Redact API             | General Availability |     ✅      |
| Reports API            |         Beta         |     ❌      |
| SMS API                | General Availability |     ✅      |
| Subaccounts API        | General Availability |     ✅      |
| Verify API             | General Availability |     ✅      |
| Verify API (Version 2) |         Beta         |     ❌      |
| Voice API              | General Availability |     ✅      |

## Troubleshooting

### Checking for Deprecated Features

Over time, the Vonage APIs evolve and add new features, change how existing 
features work, and deprecate and remove older methods and features. To help
developers know when deprecation changes are being made, the SDK will trigger
an `E_USER_DEPRECATION` warning. These warnings will not stop the execution
of code, but can be an annoyance in production environments.

To help with this, by default these notices are suppressed. In development,
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
environments, make sure the configuration option is absent, or at least
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

We allow use of any HTTPlug adapter or PSR-18 compatible HTTP client, so you can create a client with alternative configuration if you need it, for example to take account of a local proxy, or deal with something else specific to your setup.

Here's an example that reduces the default timeout to 5 seconds to avoid long delays if you have no route to our servers:

```php
$adapter_client = new Http\Adapter\Guzzle6\Client(new GuzzleHttp\Client(['timeout' => 5]));
$vonage_client = new Vonage\Client(new Vonage\Client\Credentials\Basic($api_key, $api_secret), [], $adapter_client);
```

### Accessing Response Data

When things go wrong, you'll receive an `Exception`. The Vonage exception classes `Vonage\Client\Exception\Request` and `Vonage\Client\Exception\Server` support an additional `getEntity()` method which you can use in addition to `getCode()` and `getMessage()` to find out more about what went wrong. The entity returned will typically be an object related to the operation, or the response object from the API call.

### Composer installation fails due to Guzzle Adapter

If you have a conflicting package installation that cannot co-exist with our recommended `guzzlehttp/guzzle` package, then you may install the package `vonage/client-core` along with any package satisfying the `php-http/client-implementation` requirement.

See the [Packagist page for client-implementation](https://packagist.org/providers/php-http/client-implementation) for options.

### Enabling Request/Response Logging

Our client library has support for logging the request and response for debugging via PSR-3 compatible logging mechanisms. If the `debug` option is passed into the client and a PSR-3 compatible logger is set in our client's service factory, we will use the logger for debugging purposes.

```php
$client = new \Vonage\Client(new \Vonage\Client\Credentials\Basic('abcd1234', 's3cr3tk3y'), ['debug' => true]);
$logger = new \Monolog\Logger('test');
$logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/log.txt', \Monolog\Logger::DEBUG));
$client->getFactory()->set(\PSR\Log\LoggerInterface::class, $logger);
```

**ENABLING DEBUGING LOGGING HAS THE POTENTIAL FOR LOGGING SENSITIVE INFORMATION, DO NOT ENABLE IN PRODUCTION**

## Test Suite

This library has a full test suite designed to be run with [PHPUnit](https://phpunit.de).

To run, use composer:
```
composer test
```

> Please note: this test suite is large, and may require a considerable amount of memory
> to run. If you encounter the "too many files open" error in MacOS or Linux, there is a hack to
> increase the amount of file pointers permitted. Increase the amount of files that can be open by entering the
> following on the command line (10240 is the maximum amount of pointers MacOS will open currently):
>
```
 ulimit -n 10240
```

## Contributing

This library is actively developed, and we love to hear from you! Please feel free to [create an issue][issues] or [open a pull request][pulls] with your questions, comments, suggestions and feedback.

[signup]: https://dashboard.nexmo.com/sign-up?utm_source=DEV_REL&utm_medium=github&utm_campaign=php-client-library
[doc_sms]: https://developer.nexmo.com/messaging/sms/overview
[doc_inbound]: https://developer.nexmo.com/messaging/sms/guides/inbound-sms
[doc_verify]: https://developer.nexmo.com/verify/overview
[license]: LICENSE.txt
[send_example]: https://github.com/Vonage/vonage-php-code-snippets/blob/master/sms/send-sms.php
[spec]: https://github.com/Nexmo/client-library-specification
[issues]: https://github.com/Vonage/vonage-php-core/issues
[pulls]: https://github.com/Vonage/vonage-php-core/pulls
