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

use function is_array;
use function trigger_error;

/**
 * @deprecated Please use Vonage\Voice\Client::transferCall() instead
 */
class Transfer implements JsonSerializable
{
    /**
     * @var array
     */
    protected $urls;

    public function __construct($urls)
    {
        trigger_error(
            'Vonage\Call\Transfer is deprecated, please use Vonage\Voice\Client::transferCall() instead',
            E_USER_DEPRECATED
        );

        if (!is_array($urls)) {
            $urls = [$urls];
        }

        $this->urls = $urls;
    }

    public function jsonSerialize(): array
    {
        return [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => $this->urls
            ]
        ];
    }
}
