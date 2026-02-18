<?php

namespace Vonage\Voice\NCCO\Action;

class Wait implements ActionInterface
{
    public function __construct(protected ?float $timeout = null) {}

    /**
     * @param array<array, mixed> $data
     */
    public static function factory(array $data): Wait
    {
        return new Wait($data['timeout']);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toNCCOArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toNCCOArray(): array
    {
        $returnArray = [
            'action' => 'wait',
        ];

        if (null !== $this->getTimeout()) {
            $returnArray['timeout'] = $this->getTimeout();
        }

        return $returnArray;
    }

    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getTimeout(): ?float
    {
        return $this->timeout;
    }
}
