<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Application;

class Webhook
{
    const METHOD_POST = 'POST';
    const METHOD_GET  = 'GET';

    /**
     * @var string;
     */
    protected $method;

    /**
     * @var string
     */
    protected $url;
    
    public function __construct($url, $method = self::METHOD_POST)
    {
        $this->url = $url;
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function __toString()
    {
        return $this->getUrl();
    }
}
