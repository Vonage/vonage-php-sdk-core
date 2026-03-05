<?php

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

class SIP implements EndpointInterface
{
    /**
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * @var array<string, string>
     */
    protected array $standardHeaders = [];

    public function __construct(protected string $id, array $headers = [])
    {
        $this->setHeaders($headers);
    }

    public static function factory(string $uri, array $headers = []): SIP
    {
        return new SIP($uri, $headers);
    }

    /**
     * @return array{type: string, uri: string, headers?: array<string, string>, standard_headers?: array<string, string>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array{type: string, uri: string, headers?: array<string, string>, standard_headers?: array<string, string>}
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

        if (!empty($this->getStandardHeaders())) {
            $data['standard_headers'] = $this->getStandardHeaders();
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

    /**
     * @return array<string, string>
     */
    public function getStandardHeaders(): array
    {
        return $this->standardHeaders;
    }

    /**
     * Set standard SIP INVITE headers. Unlike custom headers, these are not prepended with X-.
     * The supported key is 'User-to-User' (RFC 7433).
     *
     * @param array<string, string> $standardHeaders
     * @return $this
     */
    public function setStandardHeaders(array $standardHeaders): self
    {
        $this->standardHeaders = $standardHeaders;

        return $this;
    }

    /**
     * @return $this
     */
    public function addStandardHeader(string $key, string $value): self
    {
        $this->standardHeaders[$key] = $value;

        return $this;
    }
}
