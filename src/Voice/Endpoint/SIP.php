<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

class SIP implements EndpointInterface
{
    /**
     * @var array<string, string>
     */
    protected $headers = [];

    /**
     * @var string
     */
    protected $id;

    public function __construct(string $uri, array $headers = [])
    {
        $this->id = $uri;
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
}
