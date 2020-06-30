<?php
declare(strict_types=1);

namespace Nexmo\SMS\Message;

class SMS extends OutboundMessage
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

    public function toArray(): array
    {
        $data = ['vcal' => $this->getEvent()];

        return $this->appendUniversalOptions($data);
    }

    public function getEvent() : string
    {
        return $this->event;
    }
}
