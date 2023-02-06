<?php

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
