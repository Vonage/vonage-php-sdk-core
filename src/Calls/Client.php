<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Calls;

use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Conversations\Conversation;
use Nexmo\Entity\CollectionInterface;
use Nexmo\Entity\CollectionTrait;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Nexmo\Client\Exception;

class Client implements ClientAwareInterface, CollectionInterface
{
    use ClientAwareTrait;
    use CollectionTrait;

    public function getCollectionName()
    {
        return 'calls';
    }

    public function getCollectionPath()
    {
        return '/v1/' . $this->getCollectionName();
    }

    public function create($call)
    {
        return $this->post($call);
    }

    public function post($call)
    {
        if($call instanceof Call){
            $body = $call->getRequestData();
        } else {
            $body = $call;
        }

        $request = new Request(
            \Nexmo\Client::BASE_API . $this->getCollectionPath()
            ,'POST',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($body));
        $response = $this->client->send($request);

        if($response->getStatusCode() != '201'){
            throw $this->getException($response);
        }

        $body = json_decode($response->getBody()->getContents(), true);
        return new Conversation($body['conversation_uuid']);
    }

    public function get($call)
    {
        if(!($call instanceof Call)){
            $call = new Call($call);
        }

        $request = new Request(
            \Nexmo\Client::BASE_API . $this->getCollectionPath() . '/' . $call->getId()
            ,'GET'
        );

        $response = $this->client->send($request);

        if($response->getStatusCode() != '200'){
            throw $this->getException($response, $call);
        }

        $body = json_decode($response->getBody()->getContents(), true);
        $call->JsonUnserialize($body);

        return $call;
    }

    protected function getException(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents(), true);
        $status = $response->getStatusCode();

        if($status >= 400 AND $status < 500) {
            $e = new Exception\Request($body['error_title'], $status);
        } elseif($status >= 500 AND $status < 600) {
            $e = new Exception\Server($body['error_title'], $status);
        } else {
            $e = new Exception\Exception('Unexpected HTTP Status Code');
            throw $e;
        }

        return $e;
    }

}