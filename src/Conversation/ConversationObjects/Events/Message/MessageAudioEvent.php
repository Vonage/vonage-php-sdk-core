<?php

declare(strict_types=1);

namespace Vonage\Conversation\ConversationObjects\Events\Message;

use Vonage\Conversation\ConversationObjects\Events\BaseEvent;

class MessageAudioEvent extends BaseEvent
{
    protected string $messageType = 'audio';
    public function __construct(string $conversationId, string $from, string $url)
    {
        $this->setEventType('message');

        $this->setConversationId($conversationId);

        $body = [
            'message_type' => $this->getMessageType(),
            'audio' => [
                'url' => $url
            ]
        ];

        parent::__construct(
            $this->getEventType(),
            $from,
            $body
        );
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function setMessageType(string $messageType): MessageAudioEvent
    {
        $this->messageType = $messageType;

        return $this;
    }


}