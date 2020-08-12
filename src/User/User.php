<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\User;

use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;
use Zend\Diactoros\Request;
use Vonage\Client\Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * @deprecated This will be removed in a future version, as this API is still considered Beta
 */
class User implements EntityInterface, \JsonSerializable, JsonUnserializableInterface, ClientAwareInterface
{
    use NoRequestResponseTrait;
    use JsonSerializableTrait;
    use JsonResponseTrait;
    use ClientAwareTrait;

    protected $data = [];

    public function __construct($id = null)
    {
        $this->data['id'] = $id;
    }

    public function setName($name)
    {
        $this->data['name'] = $name;
        return $this;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function __toString()
    {
        return (string)$this->getId();
    }


    public function get()
    {
        $request = new Request(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath() . '/' . $this->getId(),
            'GET'
        );

        $response = $this->getClient()->send($request);

        if ($response->getStatusCode() != '200') {
            throw $this->getException($response);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $this->jsonUnserialize($data);

        return $this;
    }

    public function getConversations()
    {
        $response = $this->getClient()->get(
            $this->getClient()->getApiUrl() . Collection::getCollectionPath().'/'.$this->getId().'/conversations'
        );

        if ($response->getStatusCode() != '200') {
            throw $this->getException($response);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $conversationCollection = $this->getClient()->conversation();

        return $conversationCollection->hydrateAll($data);
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function jsonUnserialize(array $json)
    {
        $this->data = $json;
    }

    public function getRequestDataForConversation()
    {
        return [
            'user_id' => $this->getId()
        ];
    }

    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        // This message isn't very useful, but we shouldn't ever see it
        $errorTitle = 'Unexpected error';

        if (isset($body['code'])) {
            $errorTitle = $body['code'];
        }

        if (isset($body['description']) && $body['description']) {
            $errorTitle = $body['description'];
        }

        if (isset($body['error_title'])) {
            $errorTitle = $body['error_title'];
        }

        if ($status >= 400 and $status < 500) {
            $e = new Exception\Request($errorTitle, $status);
        } elseif ($status >= 500 and $status < 600) {
            $e = new Exception\Server($errorTitle, $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }
}
