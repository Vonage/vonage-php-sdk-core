<?php

namespace Vonage\Messages\Channel\RCS;

use Vonage\Messages\Channel\BaseMessage;

class RcsCustom extends RcsBase
{
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_CUSTOM;
    protected array $custom;

    public function __construct(
        string $to,
        string $from,
        array $custom
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->custom = $custom;
    }

    public function getCustom(): array
    {
        return $this->custom;
    }

    public function setCustom(array $custom): RcsCustom
    {
        $this->custom = $custom;
        return $this;
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();

        $returnArray['custom'] = $this->getCustom();

        return $returnArray;
    }
}
