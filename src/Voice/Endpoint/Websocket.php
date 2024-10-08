<?php

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

use function array_key_exists;

class Websocket implements EndpointInterface
{
    public const TYPE_16000 = 'audio/116;rate=16000';
    public const TYPE_8000 = 'audio/116;rate=8000';

    protected string $contentType;

    /**
     * @var array<string, string>
     */
    protected array $headers = [];

    public function __construct(protected string $id, string $rate = self::TYPE_8000, array $headers = [])
    {
        $this->setContentType($rate);
        $this->setHeaders($headers);
    }

    public static function factory(string $uri, array $data = []): Websocket
    {
        $endpoint = new Websocket($uri);

        if (array_key_exists('content-type', $data)) {
            $endpoint->setContentType($data['content-type']);
        }

        if (array_key_exists('headers', $data)) {
            $endpoint->setHeaders($data['headers']);
        }

        return $endpoint;
    }

    /**
     * @return array{type: string, uri: string, content-type?: string, headers?: array<string, string>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array{type: string, uri: string, content-type?: string, headers?: array<string, string>}
     */
    public function toArray(): array
    {
        $data = [
            'type' => 'websocket',
            'uri' => $this->id,
            'content-type' => $this->getContentType(),
        ];

        if (!empty($this->getHeaders())) {
            $data['headers'] = $this->getHeaders();
        }

        return $data;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }
}
