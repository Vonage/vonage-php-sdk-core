<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Application;

class Webhook
{
    public const METHOD_POST = 'POST';
    public const METHOD_GET = 'GET';

    /**
     * @var string|null
     */
    protected $method;

    /**
     * @var string|null
     */
    protected $url;

    public function __construct(?string $url, ?string $method = self::METHOD_POST)
    {
        $this->url = $url;
        $this->method = $method;
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
