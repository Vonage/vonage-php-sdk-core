<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Call;

class Hangup implements \JsonSerializable
{
    public function __construct()
    {
        trigger_error(
            'Nexmo\Call\Hangup is deprecated, please use Nexmo\Voice\Client::hangupCall() instead',
            E_USER_DEPRECATED
        );
    }

    public function jsonSerialize()
    {
        return [
            'action' => 'hangup'
        ];
    }
}
