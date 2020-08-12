<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Call;

/**
 * @deprecated Please use Vonage\Voice\Client::unmuteCall() instead
 */
class Unmute implements \JsonSerializable
{
    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Unmute is deprecated, please use Vonage\Voice\Client::unmuteCall() instead',
            E_USER_DEPRECATED
        );
    }

    public function jsonSerialize()
    {
        return [
            'action' => 'unmute'
        ];
    }
}
