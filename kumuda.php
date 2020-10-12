Sending a Message
To use Vonage's SMS API to send an SMS message, call the $client->sms()->send() method.

A message object is is used to create the SMS messages. Each message type can be constructed with the required parameters, and a fluent interface provides access to optional parameters.

$text = new \Vonage\SMS\Message\SMS(VONAGE_TO, VONAGE_FROM, 'Test message using PHP client library');
$text->setClientRef('test-message');
The message object is passed to the send method:

$response = $client->sms()->send($text);
Once sent, the message object can be used to access the response data.

$data = $response->current();
echo "Sent message to " . $data->getTo() . ". Balance is now " . $data->getRemainingBalance() . PHP_EOL;
Since each SMS message can be split into multiple messages, the response contains an object for each message that was generated. You can check to see how many messages were generated using the standard count() function in PHP. If you want to get the first message, you can use the current() method on the response.

$data = $response->current();
$data->getRemainingBalance();
foreach($response as $index => $data){
    $data->getRemainingBalance();
}
The send example also has full working examples.

Receiving a Message
Inbound messages are sent to your application as a webhook, and the client library provides a way to create an inbound message object from a webhook:

try {
    $inbound = \Vonage\SMS\InboundSMS::createFromGlobals();
    error_log($inbound->getText());
} catch (\InvalidArgumentException $e) {
    error_log('invalid message');
}
Signing a Message
You may also like to read the documentation about message signing.

The SMS API supports the ability to sign messages by generating and adding a signature using a "Signature Secret" rather than your API secret. The algorithms supported are:

md5hash1
md5
sha1
sha256
sha512
Both your application and Vonage need to agree on which algorithm is used. In the dashboard, visit your account settings page and under "API Settings" you can select the algorithm to use. This is also the location where you will find your "Signature Secret" (it's different from the API secret).

Create a client using these credentials and the algorithm to use, for example:

$client = new Vonage\Client(new Vonage\Client\Credentials\SignatureSecret(API_KEY, SIGNATURE_SECRET, 'sha256'));
Using this client, your SMS API messages will be sent as signed messages.

Verifying an Incoming Message Signature
You may also like to read the documentation about message signing.

If you have message signing enabled for incoming messages, the SMS webhook will include the fields sig, nonce and timestamp. To verify the signature is from Vonage, you create a Signature object using the incoming data, your signature secret and the signature method. Then use the check() method with the actual signature that was received (usually _GET['sig']) to make sure that it is correct.

$signature = new \Vonage\Client\Signature($_GET, SIGNATURE_SECRET, 'sha256');

// is it valid? Will be true or false
$isValid = $signature->check($_GET['sig']);
Using your signature secret and the other supplied parameters, the signature can be calculated and checked against the incoming signature value.

Starting a Verification
Vonage's Verify API makes it easy to prove that a user has provided their own phone number during signup, or implement second factor authentication during signin.

You can start a verification process using code like this:

$request = new \Vonage\Verify\Request('14845551212', 'My App');
$response = $client->verify()->start($request);
echo "Started verification with an id of: " . $response->getRequestId();
Once the user inputs the pin code they received, call the /check endpoint with the request ID and the pin to confirm the pin is correct.

Controlling a Verification
To cancel an in-progress verification, or to trigger the next attempt to send the confirmation code, you can pass either an existing verification object to the client library, or simply use a request ID:

$client->verify()->trigger('00e6c3377e5348cdaf567e1417c707a5');
$client->verify()->cancel('00e6c3377e5348cdaf567e1417c707a5');
Checking a Verification
In the same way, checking a verification requires the code the user provided, and the request ID:

try {
    $client->verify()->check('00e6c3377e5348cdaf567e1417c707a5', '1234');
    echo "Verification was successful (status: " . $verification->getStatus() . ")\n";
} catch (Exception $e) {
    echo "Verification failed with status " . $e->getCode()
        . " and error text \"" . $e->getMessage() . "\"\n";
}
Searching For a Verification
You can check the status of a verification, or access the results of past verifications using a request ID. The verification object will then provide a rich interface:

$client->verify()->search('00e6c3377e5348cdaf567e1417c707a5');

echo "Codes checked for verification: " . $verification->getRequestId() . PHP_EOL;
foreach($verification->getChecks() as $check){
    echo $check->getDate()->format('d-m-y') . ' ' . $check->getStatus() . PHP_EOL;
}
Payment Verification
Vonage's Verify API has SCA (Secure Customer Authentication) support, required by the PSD2 (Payment Services Directive) and used by applications that need to get confirmation from customers for payments. It includes the payee and the amount in the message.

Start the verification for a payment like this:

$request = new \Vonage\Verify\RequestPSD2('14845551212', 'My App');
$response = $client->verify()->requestPSD2($request);
echo "Started verification with an id of: " . $response['request_id'];
Once the user inputs the pin code they received, call the /check endpoint with the request ID and the pin to confirm the pin is correct.
