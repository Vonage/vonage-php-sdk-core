<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\User;

use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Entity\EntityInterface;
use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Entity\JsonSerializableTrait;
use Nexmo\Entity\JsonUnserializableInterface;
use Zend\Diactoros\Request;
use Nexmo\Client\Exception;
use Nexmo\Client\Request\RequestInterface;
use Nexmo\Entity\ArrayHydrateInterface;
use Nexmo\Entity\Psr7Trait;
use Psr\Http\Message\ResponseInterface;

class User implements
    EntityInterface,
    \JsonSerializable,
    JsonUnserializableInterface,
    ClientAwareInterface,
    ArrayHydrateInterface
{
    use JsonSerializableTrait;
    use Psr7Trait;
    use JsonResponseTrait;
    use ClientAwareTrait;

    protected $id;
    protected $name;
    protected $displayName;
    protected $imageUrl;
    protected $properties = [];

    /**
     * @deprecated
     */
    protected $data = [];

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function setName($name) : self
    {
        $this->name = $name;
        return $this;
    }

    public function setDisplayName($displayName) : self
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function setImageUrl($imageUrl) : self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function setProperties(array $properties) : self
    {
        $this->properties = $properties;
        return $this;
    }

    public function setProperty(string $key, string $value) : self
    {
        $this->properties[$key] = $value;
        return $this;
    }

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getDisplayName() : ?string
    {
        return $this->displayName;
    }

    public function getImageUrl() : ?string
    {
        return $this->imageUrl;
    }

    public function getProperties() : array
    {
        return $this->properties;
    }

    public function getProperty($key) : ?string
    {
        return array_key_exists($key, $this->properties) ? $this->properties[$key] : null;
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
        return $this->toArray();
    }

    public function jsonUnserialize(array $json)
    {
        $this->createFromArray($json);
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

    public function createFromArray(array $data) : void
    {
        if (array_key_exists('id', $data)) {
            $this->id = $data['id'];
        }

        if (array_key_exists('name', $data)) {
            $this->setName($data['name']);
        }

        if (array_key_exists('display_name', $data)) {
            $this->setDisplayName($data['display_name']);
        }

        if (array_key_exists('image_url', $data)) {
            $this->setImageUrl($data['image_url']);
        }

        if (array_key_exists('custom_data', $data)) {
            $this->setProperties($data['custom_data']);
        }
    }

    public function toArray() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'display_name' => $this->getDisplayName(),
            'image_url' => $this->getImageUrl(),
            'custom_data' => $this->getProperties(),
        ];
    }
}
