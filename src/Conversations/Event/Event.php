<?php

namespace Nexmo\Conversations\Event;

use Nexmo\Entity\ArrayHydrateInterface;

class Event implements ArrayHydrateInterface
{
    protected $id;

    protected $body = [];

    protected $conversationId;

    protected $from;
    
    protected $timestamp;

    protected $to;

    protected $type;

    public function getId() : ?string
    {
        return $this->id;
    }

    public function getBody() : ?array
    {
        return $this->body;
    }

    public function getConversationId() : ?string
    {
        return $this->conversationId;
    }

    public function getFrom() : ?string
    {
        return $this->from;
    }

    public function getTimestamp() : ?\DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getTo() : ?string
    {
        return $this->to;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function setId(string $id) : self
    {
        $this->id = $id;
        return $this;
    }

    public function setBody(array $body) : self
    {
        $this->body = $body;
        return $this;
    }

    public function setConversationId(string $conversationId) : self
    {
        $this->conversationId = $conversationId;
        return $this;
    }

    public function setFrom(string $from) : self
    {
        $this->from = $from;
        return $this;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp) : self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function setTo(string $to) : self
    {
        $this->to = $to;
        return $this;
    }

    public function setType(string $type) : self
    {
        $this->type = $type;
        return $this;
    }
    
    public function createFromArray(array $data) : void
    {
        if (array_key_exists('id', $data)) {
            $this->setId($data['id']);
        }

        if (array_key_exists('body', $data)) {
            $this->setBody($data['body']);
        }

        if (array_key_exists('conversation_id', $data)) {
            $this->setConversationId($data['conversation_id']);
        }

        if (array_key_exists('from', $data)) {
            $this->setFrom($data['from']);
        }

        if (array_key_exists('timestamp', $data)) {
            $this->setTimestamp(new \DateTimeImmutable($data['timestamp']));
        }

        if (array_key_exists('type', $data)) {
            $this->setType($data['type']);
        }
    }

    public function toArray() : array
    {
        $data = [
            'id' => $this->getId(),
            'body' => $this->getBody(),
            'type' => $this->getType(),
            'from' => $this->getFrom(),
        ];

        if (!is_null($this->getTimestamp())) {
            $data['timestamp'] = $this->getTimestamp()->format(\DateTimeInterface::RFC3339_EXTENDED);
        }

        if ('text' == $this->getType()) {
            $data['conversation_id'] = $this->getConversationId();
        }

        return $data;
    }
}