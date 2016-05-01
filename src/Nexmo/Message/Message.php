<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Message;
use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Entity\Psr7Trait;
use Nexmo\Entity\RequestArrayTrait;

/**
 * Abstract Message
 *
 * Extended by concrete message types (text, binary, etc).
 */
class Message implements MessageInterface
{
    use Psr7Trait;
    use JsonResponseTrait;
    use RequestArrayTrait;

    const TYPE = null;

    const CLASS_FLASH = 0;

    protected $responseParams = [
        'status',
        'message-id',
        'to',
        'remaining-balance',
        'message-price',
        'network'
    ];

    protected $current = 0;

    /**
     * @param string $to E.164 (international) formatted number
     * @param string $from Number or name the message is from
     * @param array  $additional Additional API Params
     */
    public function __construct($to, $from, $additional = [])
    {
        $this->requestData['to'] = (string) $to;
        $this->requestData['from'] = (string) $from;
        if(static::TYPE){
            $this->requestData['type'] = static::TYPE;
        }
        
        $this->requestData = array_merge($this->requestData, $additional);
    }
    
    public function requestDLR($dlr = true)
    {
        return $this->setRequestData('status-report-req', $dlr ? 1 : 0);
    }

    public function setClientRef($ref)
    {
        return $this->setRequestData('client-ref', (string) $ref);
    }

    public function setNetwork($network)
    {
        return $this->setRequestData('network-code', (string) $network);
    }

    public function setTTL($ttl)
    {
        return $this->setRequestData('ttl', (int) $ttl);
    }

    public function setClass($class)
    {
        return $this->setRequestData('message-class', $class);
    }

    public function count()
    {
        $data = $this->getResponseData();
        if(!isset($data['messages'])){
            return 0;
        }

        return count($data['messages']);
    }

    public function getId($index = null)
    {
        return $this->getMessageData('message-id', $index);
    }

    public function getStatus($index = null)
    {
        return $this->getMessageData('status', $index);
    }

    public function getTo($index = null)
    {
        return $this->getMessageData('to', $index);
    }

    public function getRemainingBalance($index = null)
    {
        return $this->getMessageData('remaining-balance', $index);
    }

    public function getPrice($index = null)
    {
        return $this->getMessageData('message-price', $index);
    }

    public function getNetwork($index = null)
    {
        return $this->getMessageData('network', $index);
    }
    
    protected function getMessageData($name, $index = null)
    {
        if(!isset($this->response)){
            return null;
        }

        $data = $this->getResponseData();
        if(is_null($index)){
            $index = $this->count() -1;
        }
        return $data['messages'][$index][$name];
    }

    public function offsetExists($offset)
    {
        if(in_array($offset, $this->responseParams)){
            return true;
        }

        if(is_int($offset) && $offset < $this->count()){
            return true;
        }

        return false;
    }

    public function offsetGet($offset)
    {
        if(!isset($this->response)){
            return null;
        }

        $data = $this->getResponseData();

        if(is_int($offset)){
            return $data['messages'][$offset];
        }

        $index = $this->count() -1;
        return $data['messages'][$index][$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw $this->getReadOnlyException($offset);
    }

    public function offsetUnset($offset)
    {
        throw $this->getReadOnlyException($offset);
    }

    protected function getReadOnlyException($offset)
    {
        return new \RuntimeException(sprintf(
            'can not modify `%s` using array access',
            $offset
        ));
    }

    public function current()
    {
        if(!isset($this->response)){
            return null;
        }

        $data = $this->getResponseData();
        return $data['messages'][$this->current];
    }

    public function next()
    {
        $this->current++;
    }

    public function key()
    {
        if(!isset($this->response)){
            return null;
        }

        return $this->current;
    }

    public function valid()
    {
        if(!isset($this->response)){
            return null;
        }

        $data = $this->getResponseData();
        return isset($data['messages'][$this->current]);
    }

    public function rewind()
    {
        $this->current = 0;
    }


}