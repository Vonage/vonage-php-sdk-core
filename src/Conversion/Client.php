<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Conversion;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;

use function http_build_query;
use function is_null;
use function json_decode;

class Client implements ClientAwareInterface, APIClient
{
    use ClientAwareTrait;

    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api = null)
    {
        $this->api = $api;
    }

    public function getAPIResource(): APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api
                ->setBaseUri('/conversions/')
                ->setClient($this->getClient());

            $this->api = $api;
        }

        return $this->api;
    }

    /**
     * @param $message_id
     * @param $delivered
     * @param $timestamp
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function sms($message_id, $delivered, $timestamp = null): void
    {
        $this->sendConversion('sms', $message_id, $delivered, $timestamp);
    }

    /**
     * @param $message_id
     * @param $delivered
     * @param $timestamp
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function voice($message_id, $delivered, $timestamp = null): void
    {
        $this->sendConversion('voice', $message_id, $delivered, $timestamp);
    }

    /**
     * @param $type
     * @param $message_id
     * @param $delivered
     * @param $timestamp
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    protected function sendConversion($type, $message_id, $delivered, $timestamp = null): void
    {
        $params = [
            'message-id' => $message_id,
            'delivered' => $delivered
        ];

        if ($timestamp) {
            $params['timestamp'] = $timestamp;
        }

        $uri = $type . '?' . http_build_query($params);

        $this->getAPIResource()->create([], $uri);
        $response = $this->getAPIResource()->getLastResponse();

        if (null === $response || (int)$response->getStatusCode() !== 200) {
            throw $this->getException($response);
        }
    }

    /**
     * @return ClientException\Exception|ClientException\Request|ClientException\Server
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = (int)$response->getStatusCode();

        if ($status === 402) {
            $e = new ClientException\Request('This endpoint may need activating on your account. ' .
                '"Please email support@Vonage.com for more information', $status);
        } elseif ($status >= 400 && $status < 500) {
            $e = new ClientException\Request($body['error_title'], $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new ClientException\Server($body['error_title'], $status);
        } else {
            $e = new ClientException\Exception('Unexpected HTTP Status Code (' . $status . ')');
        }

        return $e;
    }
}
