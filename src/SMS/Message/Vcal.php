<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
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

    /**
     * Vcal constructor.
     *
     * @param string $to
     * @param string $from
     * @param string $event
     */
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

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }
}
