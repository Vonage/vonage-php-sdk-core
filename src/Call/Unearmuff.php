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

/**
 * @deprecated Please use Vonage\Voice\Client::unearmuffCall() instead
 */
class Unearmuff implements JsonSerializable
{
    /**
     * Unearmuff constructor.
     */
    public function __construct()
    {
        trigger_error(
            'Vonage\Call\Unearmuff is deprecated, please use Vonage\Voice\Client::unearmuffCall() instead',
            E_USER_DEPRECATED
        );
    }

    /**
     * @return string[]
     */
    public function jsonSerialize(): array
    {
        return [
            'action' => 'unearmuff'
        ];
    }
}
