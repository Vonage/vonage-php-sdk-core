<?php

declare(strict_types=1);

namespace Vonage\SMS\Message;

class Vcard extends OutboundMessage
{
    /**
     * @var string
     */
    protected string $type = 'vcard';

    public function __construct(string $to, string $from, protected string $card)
    {
        parent::__construct($to, $from);
    }

    /**
     * @return mixed
     */
    public function toArray(): array
    {
        $data = ['vcard' => $this->getCard()];
        $data = $this->appendUniversalOptions($data);

        return $data;
    }

    public function getCard(): string
    {
        return $this->card;
    }
}
