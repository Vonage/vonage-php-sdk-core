<?php

namespace Vonage\Messages\Channel\MMS;

use Vonage\Messages\MessageObjects\VCardObject;
use Vonage\Messages\Channel\BaseMessage;

class MMSvCard extends BaseMessage
{
    protected string $channel = 'mms';
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_VCARD;
    protected VCardObject $vCard;

    public function __construct(
        string $to,
        string $from,
        VCardObject $vCard
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->vCard = $vCard;
    }

    public function toArray(): array
    {
        $returnArray = $this->getBaseMessageUniversalOutputArray();
        $returnArray['vcard'] = $this->vCard->toArray();

        return $returnArray;
    }
}
