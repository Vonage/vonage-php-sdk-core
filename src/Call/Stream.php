<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Call;

use Vonage\Call\Collection;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Entity\JsonSerializableInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Vonage\Client\Exception;

/**
 * Lightweight resource, only has put / delete.
 * 
 * @deprecated Please use Vonage\Voice\Client::streamAudio() or Vonage\Voice\Client::stopStreamAudio() instead
 */
class Stream implements JsonSerializableInterface, ClientAwareInterface
{
    use ClientAwareTrait;

    protected $id;

    protected $data = [];

    public function __construct($id = null)
    {
        trigger_error(
            'Vonage\Call\Stream is deprecated, please use Vonage\Voice\Client::streamAudio() or Vonage\Voice\Client::stopStreamAudio() instead',
            E_USER_DEPRECATED
        );

        $this->id = $id;
    }

    public function __invoke(Stream $stream = null)
    {
        if (is_null($stream)) {
            return $this;
        }

        return $this->put($stream);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUrl($url)
    {
        if (!is_array($url)) {
            $url = array($url);
        }

        $this->data['stream_url'] = $url;
    }

    public function setLoop($times)
    {
        $this->data['loop'] = (int) $times;
    }

    public function put($stream = null)
    {
        if (!$stream) {
            $stream = $this;
        }

        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId() . '/stream',
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($stream));
        $response = $this->client->send($request);
        return $this->parseEventResponse($response);
    }

    public function delete()
    {
        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId() . '/stream',
            'DELETE'
        );

        $response = $this->client->send($request);
        return $this->parseEventResponse($response);
    }

    protected function parseEventResponse(ResponseInterface $response)
    {
        if ($response->getStatusCode() != '200') {
            throw $this->getException($response);
        }

        $json = json_decode($response->getBody()->getContents(), true);

        if (!$json) {
            throw new Exception\Exception('Unexpected Response Body Format');
        }

        return new Event($json);
    }

    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        if ($status >= 400 and $status < 500) {
            $e = new Exception\Request($body['error_title'], $status);
        } elseif ($status >= 500 and $status < 600) {
            $e = new Exception\Server($body['error_title'], $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
