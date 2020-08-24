<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Call;

/**
 * @deprecated Please use Vonage\Voice\Webhook instead
 */
class Webhook implements \JsonSerializable
{
    protected $urls;

    protected $method;

    protected $type;

    public function __construct($type, $urls, $method = null)
    {
        trigger_error(
            'Vonage\Call\Webhook is deprecated, please use Vonage\Voice\Webhook instead',
            E_USER_DEPRECATED
        );

        if (!is_array($urls)) {
            $urls = [$urls];
        }

        $this->urls = $urls;
        $this->type = $type;
        $this->method = $method;
    }

    public function getType()
    {
        return $this->type;
    }

    public function add($url)
    {
        $this->urls[] = $url;
    }

    public function jsonSerialize()
    {
        $data = [
            $this->type . '_url' => $this->urls
        ];

        if (isset($this->method)) {
            $data[$this->type . '_method'] = $this->method;
        }

        return $data;
    }
}
