<?php

namespace Vonage\Messages\MessageType\Messenger;

use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\MessageType\BaseMessage;

class MessengerFile extends BaseMessage
{
    use MessengerObjectTrait;

    protected string $channel = 'messenger';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_FILE;
    protected FileObject $fileObject;

    public function __construct(
        string $to,
        string $from,
        FileObject $fileObject,
        string $category,
        string $tag = ''
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->fileObject = $fileObject;
        $this->category = $category;
        $this->tag = $tag;
    }

    public function toArray(): array
    {
        return [
            'message_type' => $this->getSubType(),
            'file' => $this->fileObject->toArray(),
            'to' => $this->getTo(),
            'from' => $this->getFrom(),
            'channel' => $this->getChannel(),
            'client_ref' => $this->getClientRef(),
            'messenger' => $this->getMessengerObject()
        ];
    }
}
