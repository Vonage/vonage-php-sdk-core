<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Conversion;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception;

class Client implements ClientAwareInterface, APIClient
{
    use ClientAwareTrait;

    /**
     * @var APIResource
     */
    protected $api;

    /**
     * Conversion Client constructor.
     *
     * @param APIResource|null $api
     */
    public function __construct(APIResource $api = null)
    {
        $this->api = $api;
    }

    /**
     * @return APIResource
     */
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
     * @param null $timestamp
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function sms($message_id, $delivered, $timestamp = null): void
    {
        $this->sendConversion('sms', $message_id, $delivered, $timestamp);
    }

    /**
     * @param $message_id
     * @param $delivered
     * @param null $timestamp
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function voice($message_id, $delivered, $timestamp = null): void
    {
        $this->sendConversion('voice', $message_id, $delivered, $timestamp);
    }

    /**
     * @param $type
     * @param $message_id
     * @param $delivered
     * @param null $timestamp
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
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
     * @param ResponseInterface $response
     * @return Exception\Exception|Exception\Request|Exception\Server
     */
    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = (int)$response->getStatusCode();

        if ($status === 402) {
            $e = new Exception\Request('This endpoint may need activating on your account. ' .
                '"Please email support@Vonage.com for more information', $status);
        } elseif ($status >= 400 && $status < 500) {
            $e = new Exception\Request($body['error_title'], $status);
        } elseif ($status >= 500 && $status < 600) {
            $e = new Exception\Server($body['error_title'], $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code (' . $status . ')');
        }

        return $e;
    }
}
