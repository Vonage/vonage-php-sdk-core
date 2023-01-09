<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity;

/**
 * Identifies the Entity as using JsonSerializable to prepare request data.
 *
 * @deprecated Please use a more appropriate hydrator, like ArrayHydrator
 */
interface JsonUnserializableInterface
{
    /**
     * Update the object state with the json data (as an array)
     *
     * @deprecated Implement ArrayHydrator instead as it is more semantically correct
     */
    public function jsonUnserialize(array $json): void;
}
