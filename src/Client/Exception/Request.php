<?php

declare(strict_types=1);

namespace Vonage\Client\Exception;

use Vonage\Entity\HasEntityTrait;
use Vonage\Entity\Psr7Trait;

class Request extends Exception
{
    use HasEntityTrait;
    use Psr7Trait;

    protected string $requestId;
    protected string $networkId;

    public function setRequestId(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setNetworkId(string $networkId): void
    {
        $this->networkId = $networkId;
    }

    public function getNetworkId(): string
    {
        return $this->networkId;
    }
}
