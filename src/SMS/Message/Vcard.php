<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

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
