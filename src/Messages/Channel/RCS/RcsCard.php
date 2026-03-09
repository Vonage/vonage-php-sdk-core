<?php

namespace Vonage\Messages\Channel\RCS;

use JsonSerializable;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\Channel\RCS\RcsCardObject;


enum RCSCardOrentation: string
{
    case Vertical = 'VERTICAL';
    case Horizontal = 'HORIZONTAL';
};

enum RCSCardAlignment: string
{
    case Right = 'RIGHT';
    case Left = 'LEFT';
};

class RcsCard extends RcsBase implements JsonSerializable
{
    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_CARD;
    protected RcsCardObject $card;
    protected ?RCSCardOrentation $orientation = null;
    protected ?RCSCardAlignment $imageAlignment = null;

    public function __construct(
        string $to,
        string $from,
        RcsCardObject $card
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->card = $card;
    }

    public function getCard(): RcsCardObject
    {
        return $this->card;
    }

    public function setCard(RcsCardObject $card): RcsCard
    {
        $this->card = $card;
        return $this;
    }

    public function toArray(): array
    {
        $returnArray = parent::toArray();

        $returnArray['card'] = $this->getCard()->toArray();

        if ($this->getImageAlignment() !== null) {
            $returnArray['rcs']['image_alignment'] = $this->getImageAlignment()->value;
        }

        if ($this->getOrientation() !== null) {
            $returnArray['rcs']['card_orientation'] = $this->getOrientation()->value;
        }

        return $returnArray;
    }

    public function jsonSerialize(): mixed
    {
        return $this->getCard()->toArray();
    }

    public function getImageAlignment(): ?RCSCardAlignment
    {
        return $this->imageAlignment;
    }

    public function setImageAlignment(?RCSCardAlignment $imageAlignment): void
    {
        $this->imageAlignment = $imageAlignment;
    }

    public function getOrientation(): ?RCSCardOrentation
    {
        return $this->orientation;
    }

    public function setOrientation(?RCSCardOrentation $orientation): void
    {
        $this->orientation = $orientation;
    }
}
