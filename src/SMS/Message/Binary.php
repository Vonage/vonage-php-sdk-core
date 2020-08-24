<?php
declare(strict_types=1);

namespace Vonage\SMS\Message;

class Binary extends OutboundMessage
{
    /**
     * @var string
     */
    protected $body;

    /**
     * @var ?int
     */
    protected $protocolId;

    /**
     * @var string
     */
    protected $type = 'binary';

    /**
     * @var string
     */
    protected $udh;

    public function __construct(string $to, string $from, string $body, string $udh, int $protocolId = null)
    {
        parent::__construct($to, $from);
        $this->body = $body;
        $this->udh = $udh;
        $this->protocolId = $protocolId;
    }

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

    public function getBody() : string
    {
        return $this->body;
    }

    public function getUdh() : string
    {
        return $this->udh;
    }

    public function getProtocolId() : ?int
    {
        return $this->protocolId;
    }
}
