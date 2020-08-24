<?php

namespace Vonage\Conversion;

use Vonage\Client\APIClient;
use Vonage\Client\Exception;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\ClientAwareInterface;
use Psr\Http\Message\ResponseInterface;

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
                ->setClient($this->getClient())
            ;

            $this->api = $api;
        }

        return $this->api;
    }

    public function sms($message_id, $delivered, $timestamp = null)
    {
        return $this->sendConversion('sms', $message_id, $delivered, $timestamp);
    }

    public function voice($message_id, $delivered, $timestamp = null)
    {
        return $this->sendConversion('voice', $message_id, $delivered, $timestamp);
    }

    protected function sendConversion($type, $message_id, $delivered, $timestamp = null)
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
        if ($response->getStatusCode() != '200') {
            throw $this->getException($response);
        }
    }

    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        if ($status === 402) {
            $e = new Exception\Request("This endpoint may need activating on your account. Please email support@Vonage.com for more information", $status);
        } elseif ($status >= 400 and $status < 500) {
            $e = new Exception\Request($body['error_title'], $status);
        } elseif ($status >= 500 and $status < 600) {
            $e = new Exception\Server($body['error_title'], $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
        }

        return $e;
    }
}
