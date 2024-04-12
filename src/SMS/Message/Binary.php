<?php

declare(strict_types=1);

namespace Vonage\SMS\Message;

class Binary extends OutboundMessage
{
    /**
     * @var string
     */
    protected string $type = 'binary';

    public function __construct(string $to, string $from, protected string $body, protected string $udh, protected ?int $protocolId = null)
    {
        parent::__construct($to, $from);
    }

    /**
     * @return mixed
     */
    public function toArray(): array
    {
        $data = [
            'body' => $this->getBody(),
            'udh' => $this->getUdh(),
        ];

        if ($this->getProtocolId()) {
            $data['protocol-id'] = $this->getProtocolId();
        }

        $data = $this->appendUniversalOptions($data);

        return $data;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getUdh(): string
    {
        return $this->udh;
    }

    public function getProtocolId(): ?int
    {
        return $this->protocolId;
    }
}
