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
use Nexmo\Conversations\Conversation;
use Nexmo\Entity\EntityInterface;
use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Entity\JsonSerializableTrait;
use Nexmo\Entity\JsonUnserializableInterface;
use Nexmo\Entity\NoRequestResponseTrait;
use Zend\Diactoros\Request;
use Nexmo\Client\Exception;
use Psr\Http\Message\ResponseInterface;

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

     public function join(Conversation $con)
    {
        return $this->sendAction($con, 'join');
    }

    public function invite(Conversation $user)
    {
        return $this->sendAction($con, 'invite');
    }

    public function leave(Conversation $user)
    {
        return $this->sendAction($user, 'leave');
    }

    public function sendAction(Conversation $con, $action, $channel = 'app') {
        $body = $this->getRequestDataForConversation();
        $body['action'] = $action;
        $body['channel'] = ['type' => $channel];

        $response = $this->getClient()->post(
            \Nexmo\Client::BASE_API . \Nexmo\Conversations\Collection::getCollectionPath() . '/' . $con->getId() .'/members',
            $body
        );

        if($response->getStatusCode() != '200'){
            throw $this->getException($response);
        }

        $body = json_decode($response->getBody()->getContents(), true);
        print_r($body);die;
        $user = new User($body['user_id']);
        $user->jsonUnserialize($body);
        $user->setClient($this->getClient());

        return $user;

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

        if($status >= 400 AND $status < 500) {
            $e = new Exception\Request($errorTitle, $status);
        } elseif($status >= 500 AND $status < 600) {
            $e = new Exception\Server($errorTitle, $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }


}