<?php

namespace Vonage\Verify2\Request;

use Vonage\Verify2\VerifyObjects\VerificationLocale;

abstract class BaseVerifyRequest implements RequestInterface
{
    protected ?VerificationLocale $locale = null;

    protected int $timeout = 300;

    protected ?string $clientRef = null;

    protected int $length = 4;

    protected string $brand;

    protected array $workflows = [];
}