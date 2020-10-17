<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\SMS;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\ThrottleException;
use Vonage\SMS\Message\Message;

class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $api;

    /**
     * Client constructor.
     *
     * @param APIResource $api
     */
    public function __construct(APIResource $api)
    {
        $this->api = $api;
    }

    /**
     * @return APIResource
     */
    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    /**
     * @param Message $message
     * @return Collection
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function send(Message $message): Collection
    {
        try {
            $response = $this->api->create($message->toArray(), '/sms/json');
            return new Collection($response);
        } catch (ThrottleException $e) {
            sleep($e->getTimeout());
            return $this->send($message);
        }
    }

    /**
     * @param string $number
     * @param int $pin
     * @return SentSMS
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function sendTwoFactor(string $number, int $pin): SentSMS
    {
        $response = $this->api->create(
            ['to' => $number, 'pin' => $pin],
            '/sc/us/2fa/json'
        );

        return new SentSMS($response['messages'][0]);
    }

    /**
     * @param string $number
     * @param array $templateReplacements
     * @return Collection
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function sendAlert(string $number, array $templateReplacements): Collection
    {
        $response = $this->api->create(
            ['to' => $number] + $templateReplacements,
            '/sc/us/alert/json'
        );
        return new Collection($response);
    }
}
