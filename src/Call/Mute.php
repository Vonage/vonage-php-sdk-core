<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Call;

use JsonSerializable;

/**
 * @deprecated Please use Vonage\Voice\Client::muteCall() instead
 */
class Mute implements JsonSerializable
{
    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Mute is deprecated, please use Vonage\Voice\Client::muteCall() instead',
            E_USER_DEPRECATED
        );
    }

    public function jsonSerialize(): array
    {
        return ['action' => 'mute'];
    }
}
