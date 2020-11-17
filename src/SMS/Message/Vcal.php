<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS\Message;

class Vcal extends OutboundMessage
{
    /**
     * @var string
     */
    protected $event;

    /**
     * @var string
     */
    protected $type = 'vcal';

    public function __construct(string $to, string $from, string $event)
    {
        parent::__construct($to, $from);

        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function toArray(): array
    {
        $data = ['vcal' => $this->getEvent()];
        $data = $this->appendUniversalOptions($data);

        return $data;
    }

    public function getEvent(): string
    {
        return $this->event;
    }
}
