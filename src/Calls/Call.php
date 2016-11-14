<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Calls;

use Nexmo\Conversations\Conversation;
use Nexmo\Entity\CollectionAwareInterface;
use Nexmo\Entity\CollectionAwareTrait;
use Nexmo\Entity\EntityInterface;
use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Entity\JsonSerializableTrait;
use Nexmo\Entity\JsonUnserializableInterface;
use Nexmo\Entity\NoRequestResponseTrait;
use Nexmo\Entity\Psr7Trait;

/**
 * Class Call
 * @method Collection getCollection()
 */
class Call implements EntityInterface, \JsonSerializable, JsonUnserializableInterface, CollectionAwareInterface
{
    use NoRequestResponseTrait;
    use JsonSerializableTrait;
    use JsonResponseTrait;
    use CollectionAwareTrait;

    const WEBHOOK_ANSWER = 'answer';
    const WEBHOOK_EVENT  = 'event';

    const TIMER_LENGTH  = 'length';
    const TIMER_RINGING = 'ringing';

    const TIMEOUT_MACHINE = 'machine';

    protected $id;

    protected $to;

    protected $from;

    /**
     * @var Webhook[]
     */
    protected $webhooks = [];

    protected $data = [];

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function get()
    {
        return $this->getCollection()->get($this);
    }

    public function put($payload)
    {
        return $this->getCollection()->put($payload, $this);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTo($endpoint)
    {
        if(!($endpoint instanceof Endpoint)){
            $endpoint = new Endpoint($endpoint);
        }

        $this->to = $endpoint;
        return $this;
    }

    /**
     * @return Endpoint
     */
    public function getTo()
    {
        if($this->lazyLoad()){
            return new Endpoint($this->data['to']['number'], $this->data['to']['type']);
        }

        return $this->to;
   }

    public function setFrom($endpoint)
    {
        if(!($endpoint instanceof Endpoint)){
            $endpoint = new Endpoint($endpoint);
        }

        $this->from = $endpoint;
        return $this;
    }

    /**
     * @return Endpoint
     */
    public function getFrom()
    {
        if($this->lazyLoad()){
            return new Endpoint($this->data['from']['number'], $this->data['from']['type']);
        }

        return $this->from;
    }

    public function setWebhook($type, $url = null, $method = null)
    {
        if($type instanceof Webhook){
            $this->webhooks[$type->getType()] = $type;
            return $this;
        }

        if(is_null($url)){
            throw new \InvalidArgumentException('must provide `Nexmo\Calls\Webhook` object, or a type and url: missing url' );
        }

        $this->webhooks[$type] = new Webhook($type, $url, $method);
        return $this;
    }

    public function setTimer($type, $length)
    {
        $this->data[$type . '_timer'] = $length;
    }

    public function setTimeout($type, $length)
    {
        $this->data[$type . '_timeout'] = $length;
    }

    public function getStatus()
    {
        if($this->lazyLoad()){
            return $this->data['status'];
        }
    }

    public function getDirection()
    {
        if($this->lazyLoad()){
            return $this->data['direction'];
        }
    }

    public function getConversation()
    {
        if($this->lazyLoad()){
            return new Conversation($this->data['conversation_uuid']);
        }
    }

    protected function lazyLoad()
    {
        if(!empty($this->data)){
            return true;
        }

        if(isset($this->id)){
            $this->getCollection()->get($this);
            return true;
        }

        return false;
    }

    public function jsonSerialize()
    {
        $data = $this->data;

        if(isset($this->to)){
            $data['to'] = [$this->to->jsonSerialize()];
        }

        if(isset($this->from)){
            $data['from'] = $this->from->jsonSerialize();
        }

        foreach($this->webhooks as $webhook){
            $data = array_merge($data, $webhook->jsonSerialize());
        }

        return $data;
    }

    public function JsonUnserialize(array $json)
    {
        $this->data = $json;
        $this->id = $json['uuid'];
    }
}