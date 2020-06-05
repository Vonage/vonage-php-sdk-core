<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Call;

/**
 * @deprecated Please use Nexmo\Voice\Client::unearmuffCall() instead
 */
class Unearmuff implements \JsonSerializable
{
    public function __construct()
    {
        trigger_error(
            'Nexmo\Call\Unearmuff is deprecated, please use Nexmo\Voice\Client::unearmuffCall() instead',
            E_USER_DEPRECATED
        );
    }

    public function jsonSerialize()
    {
        return [
            'action' => 'unearmuff'
        ];
    }
}
