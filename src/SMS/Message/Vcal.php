<?php

declare(strict_types=1);

namespace Vonage\SMS\Message;

class Vcal extends OutboundMessage
{
    /**
     * @var string
     */
    protected string $type = 'vcal';

    public function __construct(string $to, string $from, protected string $event)
    {
        parent::__construct($to, $from);
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
