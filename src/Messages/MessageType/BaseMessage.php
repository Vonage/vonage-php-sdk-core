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

    public const MESSAGES_SUBTYPE_TEXT = 'text';
    public const MESSAGES_SUBTYPE_IMAGE = 'image';
    public const MESSAGES_SUBTYPE_VCARD = 'vcard';
    public const MESSAGES_SUBTYPE_AUDIO = 'audio';
    public const MESSAGES_SUBTYPE_VIDEO = 'video';
    public const MESSAGES_SUBTYPE_FILE = 'file';
    public const MESSAGES_SUBTYPE_TEMPLATE = 'template';
    public const MESSAGES_SUBTYPE_CUSTOM = 'custom';
}