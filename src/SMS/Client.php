<?php
declare(strict_types=1);

namespace Nexmo\SMS;

use Nexmo\Client\APIClient;
use Nexmo\Client\APIResource;
use Nexmo\Client\Exception\ThrottleException;
use Nexmo\SMS\Message\Message;

class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api)
    {
        $this->api = $api;
    }

    public function getAPIResource() : APIResource
    {
        return $this->api;
    }

    public function send(Message $message) : Collection
    {
        try {
            $response = $this->api->create($message->toArray(), '/sms/json');
            return new Collection($response);
        } catch (ThrottleException $e) {
            sleep($e->getTimeout());
            return $this->send($message);
        }
    }

    public function sendTwoFactor(string $number, int $pin) : SentSMS
    {
        $response = $this->api->create(
            ['to' => $number, 'pin' => $pin],
            '/sc/us/2fa/json'
        );

        return new SentSMS($response['messages'][0]);
    }

    public function sendAlert(string $number, array $templateReplacements)
    {
        $response = $this->api->create(
            ['to' => $number] + $templateReplacements,
            '/sc/us/alert/json'
        );
        return new Collection($response);
    }
}
