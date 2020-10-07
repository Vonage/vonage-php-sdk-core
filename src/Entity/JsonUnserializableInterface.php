<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Entity;

/**
 * Identifies the Entity as using JsonSerializable to prepare request data.
 * @deprecated Please use a more appropriate hydrator, like ArrayHydrator
 */
interface JsonUnserializableInterface
{
    /**
     * Update the object state with the json data (as an array)
     *
     * @param array $json
     * @deprecated Implement ArrayHydrator instead as it is more semantically correct
     */
    public function jsonUnserialize(array $json): void;
}
