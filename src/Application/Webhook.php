<?php

declare(strict_types=1);

namespace Vonage\Application;

class Webhook implements \Stringable
{
    public const METHOD_POST = 'POST';
    public const METHOD_GET = 'GET';
    public ?string $socketTimeout = null;
    public ?string $connectionTimeout = null;

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
        return (string) $this->getUrl();
    }

    public function getSocketTimeout(): ?string
    {
        return $this->socketTimeout;
    }

    public function setSocketTimeout(?string $socketTimeout): static
    {
        $this->socketTimeout = $socketTimeout;

        return $this;
    }

    public function getConnectionTimeout(): ?string
    {
        return $this->connectionTimeout;
    }

    public function setConnectionTimeout(?string $connectionTimeout): static
    {
        $this->connectionTimeout = $connectionTimeout;

        return $this;
    }
}
