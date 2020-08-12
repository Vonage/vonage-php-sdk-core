<?php
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

    public static function factory(string $uri, array $headers = []) : SIP
    {
        $endpoint = new SIP($uri, $headers);
        return $endpoint;
    }

    /**
     * @return array{type: string, uri: string, headers?: array<string, string>}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array{type: string, uri: string, headers?: array<string, string>}
     */
    public function toArray() : array
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

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    public function getHeaders() : array
    {
        return $this->headers;
    }

    public function addHeader(string $key, string $value) : self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setHeaders(array $headers) : self
    {
        $this->headers = $headers;
        return $this;
    }
}
