<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Call;

use JsonSerializable;

class Hangup implements JsonSerializable
{
    /**
     * Hangup constructor.
     */
    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Hangup is deprecated, please use Vonage\Voice\Client::hangupCall() instead',
            E_USER_DEPRECATED
        );
    }

    /**
     * @return string[]
     */
    public function jsonSerialize(): array
    {
        return [
            'action' => 'hangup'
        ];
    }
}
