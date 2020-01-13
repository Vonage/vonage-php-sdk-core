<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Call\NCCO;

class Transfer implements \JsonSerializable, NCCOInterface
{
    protected $urls;

    public function __construct($urls)
    {
        if (!is_array($urls)) {
            $urls = array($urls);
        }

        $this->urls = $urls;
    }

    public function toArray() : array
    {
        return [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => $this->urls
            ]
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
