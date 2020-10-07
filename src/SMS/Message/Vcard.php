<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
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

    /**
     * Vcard constructor.
     *
     * @param string $to
     * @param string $from
     * @param string $card
     */
    public function __construct(string $to, string $from, string $card)
    {
        parent::__construct($to, $from);

        $this->card = $card;
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

    /**
     * @return string
     */
    public function getCard() : string
    {
        return $this->card;
    }
}
