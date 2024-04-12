<?php

declare(strict_types=1);

namespace Vonage\Voice;

class Webhook
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    public function __construct(protected string $url, protected string $method = self::METHOD_POST)
    {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
