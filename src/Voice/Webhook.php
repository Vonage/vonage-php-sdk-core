<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Voice;

class Webhook
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

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

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
