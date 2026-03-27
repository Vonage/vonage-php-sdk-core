<?php

declare(strict_types=1);

namespace Vonage\Messages\Channel\RCS;

use LengthException;
use Vonage\Messages\Channel\BaseMessage;
use Vonage\Messages\MessageTraits\SuggestionsTrait;

class RcsCarousel extends RcsBase
{
    use SuggestionsTrait;

    protected string $subType = BaseMessage::MESSAGES_SUBTYPE_CAROUSEL;
    protected RcsCardCollection $cards;

    public function __construct(
        string $to,
        string $from,
        array $cards
    ) {
        $this->to = $to;
        $this->from = $from;
        $this->cards = new RcsCardCollection($cards);
    }

    public function getCards(): RcsCardCollection
    {
        return $this->cards;
    }

    public function toArray(): array
    {
        if (count($this->cards) < RcsCardCollection::MIN_CARDS) {
            throw new LengthException(
                "You need to have at least " . RcsCardCollection::MIN_CARDS . " cards for an RCS carousel message"
            );
        }
        $returnArray = parent::toArray();
        $returnArray['carousel'] = ['cards' => $this->cards->toArray()];

        if (isset($this->suggestions) && count($this->suggestions) > 0) {
            $returnArray['suggestions'] = $this->suggestions->toArray();
        }

        return $returnArray;
    }
}
