<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

use JsonSerializable;

interface EndpointInterface extends JsonSerializable
{

    public function getId(): string;

    /**
     * @return array<string, array>
     */
    public function toArray(): array;
}
