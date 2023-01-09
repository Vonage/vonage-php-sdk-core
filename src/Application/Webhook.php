<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Application;

class Webhook implements \Stringable
{
    public const METHOD_POST = 'POST';
    public const METHOD_GET = 'GET';

    public function __construct(protected ?string $url, protected ?string $method = self::METHOD_POST)
    {
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }
}
