<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

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
