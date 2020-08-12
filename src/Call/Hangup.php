<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Call;

class Hangup implements \JsonSerializable
{
    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Hangup is deprecated, please use Vonage\Voice\Client::hangupCall() instead',
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
