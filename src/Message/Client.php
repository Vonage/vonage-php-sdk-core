<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Message;

use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\Exception;
use Zend\Diactoros\Request;

/**
 * Class Client
 * @method Text sendText(string $to, string $from, string $text, array $additional = []) Send a Test Message
 */
class Client implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @param Message|array $message
     * @return Message
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function send($message)
    {
        if(!($message instanceof MessageInterface)){
            $message = $this->createMessageFromArray($message);
        }

        $params = $message->getRequestData();
        
        $request = new Request(
            \Nexmo\Client::BASE_REST . '/sms/json?' . http_build_query($params)
            ,'POST'
        );

        $request->getBody()->write(json_encode($params));
        $message->setRequest($request);
        $response = $this->client->send($request);
        $message->setResponse($response);

        //check for valid data, as well as an error response from the API
        $data = $message->getResponseData();
        if(!isset($data['messages'])){
            throw new Exception\Exception('unexpected response from API');
        }

        //normalize errors (client vrs server)
        foreach($data['messages'] as $part){
            switch($part['status']){
                case '0':
                    continue; //all okay
                case '5':
                    $e = new Exception\Server($part['error-text'], $part['status']);
                    $e->setEntity($message);
                    throw $e;
                default:
                    $e = new Exception\Request($part['error-text'], $part['status']);
                    $e->setEntity($message);
                    throw $e;
            }
        }

        return $message;
    }

    /**
     * @param array $message
     * @return Message
     */
    protected function createMessageFromArray($message)
    {
        if(!is_array($message)){
            throw new \RuntimeException('message must implement `' . MessageInterface::class . '` or be an array`');
        }

        foreach(['to', 'from'] as $param){
            if(!isset($message[$param])){
                throw new \InvalidArgumentException('missing expected key `' . $param . '`');
            }
        }

        $to = $message['to'];
        $from = $message['from'];

        unset($message['to']);
        unset($message['from']);

        return new Message($to, $from, $message);
    }

    /**
     * Convenience feature allowing messages to be sent without creating a message object first.
     *
     * @param $name
     * @param $arguments
     * @return MessageInterface
     */
    public function __call($name, $arguments)
    {
        if(!(strstr($name, 'send') !== 0)){
            throw new \RuntimeException(sprintf(
                '`%s` is not a valid method on `%s`',
                $name,
                get_class($this)
            ));
        }

        $class = substr($name, 4);
        $class = 'Nexmo\\Message\\' . ucfirst(strtolower($class));

        if(!class_exists($class)){
            throw new \RuntimeException(sprintf(
                '`%s` is not a valid method on `%s`',
                $name,
                get_class($this)
            ));
        }

        $reflection = new \ReflectionClass($class);
        $message = $reflection->newInstanceArgs($arguments);

        return $this->send($message);
    }
}