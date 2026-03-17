<?php

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

use function array_key_exists;

class Websocket implements EndpointInterface
{
    public const TYPE_8000 = 'audio/l16;rate=8000';
    public const TYPE_16000 = 'audio/l16;rate=16000';
    public const TYPE_24000 = 'audio/l16;rate=24000';

    protected string $contentType;

    /**
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * @var array{type: string, value?: string}|null
     */
    protected ?array $authorization = null;

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

        if (array_key_exists('authorization', $data)) {
            $endpoint->setAuthorization($data['authorization']);
        }

        return $endpoint;
    }

    /**
     * @return array{type: string, uri: string, content-type?: string, headers?: array<string, string>, authorization?: array{type: string, value?: string}}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array{type: string, uri: string, content-type?: string, headers?: array<string, string>, authorization?: array{type: string, value?: string}}
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

        if (null !== $this->getAuthorization()) {
            $data['authorization'] = $this->getAuthorization();
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

    /**
     * @return array{type: string, value?: string}|null
     */
    public function getAuthorization(): ?array
    {
        return $this->authorization;
    }

    /**
     * Set the authorization configuration for the WebSocket opening handshake.
     * The type must be 'vonage' or 'custom'. When type is 'custom', a value must be provided.
     *
     * @param array{type: string, value?: string} $authorization
     */
    public function setAuthorization(array $authorization): self
    {
        $this->authorization = $authorization;

        return $this;
    }
}
