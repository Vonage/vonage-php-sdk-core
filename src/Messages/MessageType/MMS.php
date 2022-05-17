<?php

namespace Vonage\Messages\MessageType;

class MMS extends BaseMessage
{
    protected string $subtype = BaseMessage::MESSAGES_SUBTYPE_IMAGE;

    protected array $validSubtypes = [
        BaseMessage::MESSAGES_SUBTYPE_IMAGE,
        BaseMessage::MESSAGES_SUBTYPE_VCARD,
        BaseMessage::MESSAGES_SUBTYPE_AUDIO,
        BaseMessage::MESSAGES_SUBTYPE_VIDEO
    ];

    protected string $channel = 'mms';
}