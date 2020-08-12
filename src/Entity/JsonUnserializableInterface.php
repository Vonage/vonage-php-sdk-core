<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Entity;

/**
 * Identifies the Entity as using JsonSerializable to prepare request data.
 * @deprecated Please use a more appropriate hydrator, like ArrayHydrator
 */
interface JsonUnserializableInterface
{
    /**
     * Update the object state with the json data (as an array)
     * @deprecated Implement ArrayHydrator instead as it is more semantically correct
     * @param $json
     * @return null
     */
    public function jsonUnserialize(array $json);
}
