<?php

namespace Vonage\Messages\MessageType;

abstract class BaseMessage implements Message
{
    protected string $messageType;
    protected string $to;
    protected string $from;
    protected string $channel;
    protected string $subtype;
    protected array $validSubtypes;
    protected string $clientRef;

    public const MESSAGES_SUBTYPE_TEXT = 'text';
    public const MESSAGES_SUBTYPE_IMAGE = 'image';
    public const MESSAGES_SUBTYPE_VCARD = 'vcard';
    public const MESSAGES_SUBTYPE_AUDIO = 'audio';
    public const MESSAGES_SUBTYPE_VIDEO = 'video';
    public const MESSAGES_SUBTYPE_FILE = 'file';
    public const MESSAGES_SUBTYPE_TEMPLATE = 'template';
    public const MESSAGES_SUBTYPE_CUSTOM = 'custom';

    public function getClientRef(): string
    {
        return $this->clientRef;
    }

    public function setClientRef(string $clientRef): void
    {
        $this->clientRef = $clientRef;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    public function getSubType(): string
    {
        return $this->subtype;
    }

    public function setSubType(string $subType): void
    {
        $this->subtype = $subType;
    }

    public function setMessageType(string $messageType): void
    {
        $this->messageType = $messageType;
    }
}