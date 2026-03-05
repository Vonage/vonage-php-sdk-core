<?php

declare(strict_types=1);

namespace Vonage\Voice\NCCO\Action;

use function array_key_exists;
use function filter_var;
use function is_null;

class Wait implements ActionInterface
{
    protected ?float $timeout = null;

    public function __construct()
    {
    }

    /**
     * @param array{timeout?: float} $data
     */
    public static function factory(array $data): Wait
    {
        $wait = new Wait();

        if (array_key_exists('timeout', $data)) {
            $wait->setTimeout(
                filter_var($data['timeout'], FILTER_VALIDATE_FLOAT, ['flags' => FILTER_NULL_ON_FAILURE])
            );
        }

        return $wait;
    }

    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    /**
     * @return $this
     */
    public function setTimeout(float $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return array{action: string, timeout?: float}
     */
    public function jsonSerialize(): array
    {
        return $this->toNCCOArray();
    }

    /**
     * @return array{action: string, timeout?: float}
     */
    public function toNCCOArray(): array
    {
        $data = ['action' => 'wait'];

        if (!is_null($this->getTimeout())) {
            $data['timeout'] = $this->getTimeout();
        }

        return $data;
    }
}
