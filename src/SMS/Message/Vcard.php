<?php
declare(strict_types=1);

namespace Vonage\SMS\Message;

class Vcard extends OutboundMessage
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
        $data = ['vcard' => $this->getCard()];
        $data = $this->appendUniversalOptions($data);

        return $data;
    }

    public function getCard() : string
    {
        return $this->card;
    }
}
