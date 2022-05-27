<?php

namespace Vonage\Messages\MessageType\WhatsApp;

use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageType\BaseMessage;

class WhatsAppFile extends BaseMessage
{
    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_FILE;
    protected FileObject $fileObject;

    public function __construct(
        string $to,
        string $from,
        FileObject $fileObject
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->fileObject = $fileObject;
    }

    public function toArray(): array
    {
        return [
            'message_type' => $this->getSubType(),
            'file' => $this->fileObject->toArray(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef()
        ];
    }
}