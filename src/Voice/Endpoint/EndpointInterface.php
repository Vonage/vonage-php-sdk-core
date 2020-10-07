<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Voice\Endpoint;

use JsonSerializable;

interface EndpointInterface extends JsonSerializable
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return array<string, array>
     */
    public function toArray(): array;
}
