<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Call;

use RuntimeException;
use Nexmo\Entity\EntityInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Conversations\Conversation;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Entity\JsonSerializableTrait;
use Nexmo\Entity\NoRequestResponseTrait;
use Nexmo\Entity\JsonUnserializableInterface;

/**
 * Class Call
 */
class Call implements EntityInterface, \JsonSerializable, JsonUnserializableInterface, ClientAwareInterface
{
    use NoRequestResponseTrait;
    use JsonSerializableTrait;
    use JsonResponseTrait;
    use ClientAwareTrait;

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

    protected $subresources = [];

    /**
     * @var Callable
     */
    protected $conversation = null;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTo(Endpoint $endpoint)
    {
        $this->to = $endpoint;
        return $this;
    }

    public function getTo() : Endpoint
    {
        return $this->to;
    }

    public function setFrom(Endpoint $endpoint)
    {
        $this->from = $endpoint;
        return $this;
    }

    public function getFrom() : Endpoint
    {
        return $this->from;
    }

    public function setWebhook($type, $url = null, $method = null)
    {
        if ($type instanceof Webhook) {
            $this->webhooks[$type->getType()] = $type;
            return $this;
        }

        if (is_null($url)) {
            throw new \InvalidArgumentException('must provide `Nexmo\Call\Webhook` object, or a type and url: missing url');
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

    public function setNcco($ncco)
    {
        $this->data['ncco'] = $ncco;
        return $this;
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function getDirection()
    {
        return $this->data['direction'];
    }

    public function getConversation() : Conversation
    {
        if ($this->conversation != null) {
            $convo = $this->conversation;
            return $convo();
        }

        throw new RuntimeException('Conversation was not properly hydrated for this Call');
    }

    public function setConversation($conversation)
    {
        $this->conversation = $conversation;
    }

    public function toArray() : array
    {
        $data = $this->data;

        if (isset($this->to)) {
            $data['to'] = [$this->to->jsonSerialize()];
        }

        if (isset($this->from)) {
            $data['from'] = $this->from->jsonSerialize();
        }

        foreach ($this->webhooks as $webhook) {
            $data = array_merge($data, $webhook->jsonSerialize());
        }

        return $data;
    }

    /**
     * Not for public consumption
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function createFromArray(array $data)
    {
        $this->data = $data;
        $this->id = $data['uuid'];

        if (array_key_exists('to', $data)) {
            $this->setTo(new Endpoint($data['to']['number'], $data['to']['type']));
        }

        if (array_key_exists('from', $data)) {
            $this->setFrom(new Endpoint($data['from']['number'], $data['from']['type']));
        }
    }

    public function jsonUnserialize(array $json)
    {
        $this->createFromArray($json);
    }
}
