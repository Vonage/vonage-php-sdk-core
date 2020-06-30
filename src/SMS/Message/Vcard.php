<?php
declare(strict_types=1);

namespace Nexmo\SMS\Message;

class SMS extends OutboundMessage
{
    /**
     * @var string
     */
    protected $card;

    /**
     * @var string
     */
    protected $type = 'vcard';

    public function __construct(string $to, string $from, string $card)
    {
        parent::__construct($to, $from);
        $this->card = $card;
    }

    public function toArray(): array
    {
        $data = ['card' => $this->getCard()];

        return $this->appendUniversalOptions($data);
    }

    public function getCard() : string
    {
        return $this->card;
    }
}
