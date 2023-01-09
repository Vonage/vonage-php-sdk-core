<?php

namespace Vonage\Messages\Channel\WhatsApp;

use Vonage\Messages\MessageObjects\FileObject;
use Vonage\Messages\Channel\BaseMessage;

class WhatsAppFile extends BaseMessage
{
    protected string $channel = 'whatsapp';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_FILE;

    public function __construct(
        string $to,
        string $from,
        protected FileObject $fileObject
    ) {
        $this->to = $to;
        $this->from = $from;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['file'] = $this->fileObject->toArray();

        return $returnArray;
    }
}