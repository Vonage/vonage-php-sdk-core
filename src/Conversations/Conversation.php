<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Conversations;


use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Entity\EntityInterface;
use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Entity\JsonSerializableTrait;
use Nexmo\Entity\JsonUnserializableInterface;
use Nexmo\Entity\NoRequestResponseTrait;
use Zend\Diactoros\Request;

class Conversation implements EntityInterface, \JsonSerializable, JsonUnserializableInterface, ClientAwareInterface
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

    public function setDisplayName($name)
    {
        $this->data['display_name'] = $name;
        return $this;
    }

    public function getId()
    {
        if (isset($this->data['uuid'])) {
            return $this->data['uuid'];
        }
        return $this->data['id'];
    }

    public function __toString()
    {
        return (string)$this->getId();
    }


    public function get()
    {
        $request = new Request(
            \Nexmo\Client::BASE_API . Collection::getCollectionPath() . '/' . $this->getId()
            ,'GET'
        );

        $response = $this->getClient()->send($request);

        if($response->getStatusCode() != '200'){
            throw $this->getException($response);
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $this->jsonUnserialize($data);

        return $this;
    }


    public function jsonSerialize()
    {
        return $this->data;
    }

    public function jsonUnserialize(array $json)
    {
        $this->data = $json;
    }

}