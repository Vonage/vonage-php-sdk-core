<?php
declare(strict_types=1);

namespace Vonage\Voice;

class Webhook
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    /**
     * @var string
     */
    protected $method;

    /**
     * @var Webhook::METHOD_*
     */
    protected $url;

    public function __construct(string $url, string $method = self::METHOD_POST)
    {
        $this->url = $url;
        $this->method = $method;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function getUrl() : string
    {
        return $this->url;
    }
}
