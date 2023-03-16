<?php

namespace Vonage\Verify2\VerifyObjects;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class VerificationWorkflow implements ArrayHydrateInterface
{
    public function __construct(protected string $channel, protected string $to, protected string $from = '')
    {
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): static
    {
        $this->to = $to;

        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): static
    {
        $this->from = $from;

        return $this;
    }

    public function fromArray(array $data): static
    {
        $this->channel = $data['channel'];
        $this->to = $data['to'];

        if (array_key_exists('from', $data)) {
            $this->from = $data['from'];
        }

        return $this;
    }

    public function toArray(): array
    {
        $returnArray = [
            'channel' => $this->getChannel(),
            'to' => $this->getTo()
        ];

        if (!empty($this->getFrom())) {
            $returnArray['from'] = $this->getFrom();
        }

        return $returnArray;

    }
}