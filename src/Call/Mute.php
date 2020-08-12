<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Call;

/**
 * @deprecated Please use Vonage\Voice\Client::muteCall() instead
 */
class Mute implements \JsonSerializable
{
    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Mute is deprecated, please use Vonage\Voice\Client::muteCall() instead',
            E_USER_DEPRECATED
        );
    }

    public function jsonSerialize()
    {
        return [
            'action' => 'mute'
        ];
    }
}
