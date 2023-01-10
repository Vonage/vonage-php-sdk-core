<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
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
use Vonage\SMS\Message\SMS;

use function sleep;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function send(Message $message): Collection
    {
        if ($message->getErrorMessage()) {
            trigger_error(
                $message->getErrorMessage(),
                E_USER_WARNING
            );
        }

        try {
            $response = $this->api->create($message->toArray(), '/sms/json');

            return new Collection($response);
        } catch (ThrottleException $e) {
            sleep($e->getTimeout());

            return $this->send($message);
        }
    }

    /**
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
