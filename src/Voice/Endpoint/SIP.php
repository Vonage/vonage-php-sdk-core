<?php

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

class SIP implements EndpointInterface
{
    /**
     * @var array<string, string>
     */
    protected $headers = [];

    protected bool $standardHeadersUserToUser = false;

    public function __construct(protected string $id, array $headers = [])
    {
        $this->setHeaders($headers);
    }

    public static function factory(string $uri, array $headers = []): SIP
    {
        return new SIP($uri, $headers);
    }

    /**
     * @return array{type: string, uri: string, headers?: array<string, string>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array{type: string, uri: string, headers?: array<string, string>}
     */
    public function toArray(): array
    {
        $data = [
            'type' => 'sip',
            'uri' => $this->id,
        ];

        if (!empty($this->getHeaders())) {
            $data['headers'] = $this->getHeaders();
        }

        if ($this->getStandardHeadersUserToUser()) {
            $data['standard_headers'] = ['User-to-User'];
        }

        return $data;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return $this
     */
    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function setStandardHeadersUserToUser(bool $value): static
    {
        $this->standardHeadersUserToUser = $value;

        return $this;
    }

    public function getStandardHeadersUserToUser(): bool
    {
        return $this->standardHeadersUserToUser;
    }
}
