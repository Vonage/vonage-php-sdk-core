<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Entity\Hydrator;

/**
 * Interface for allowing an entity to be converted to/from an array
 * While the built-in `JsonSerializable` interface is nice, it's not
 * always semantically correct. This provides a much more clear set
 * of functions for handling this. Ideally, if an entity also
 * implements `JsonSerializable`, those functions can just wrap these
 */
interface ArrayHydrateInterface
{
    public function fromArray(array $data);

    public function toArray(): array;
}
