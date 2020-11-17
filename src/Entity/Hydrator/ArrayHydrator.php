<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity\Hydrator;

class ArrayHydrator implements HydratorInterface
{
    /**
     * @var ArrayHydrateInterface
     */
    protected $prototype;

    public function hydrate(array $data): ArrayHydrateInterface
    {
        $object = clone $this->prototype;
        $object->fromArray($data);

        return $object;
    }

    /**
     * @param $object
     */
    public function hydrateObject(array $data, $object)
    {
        $object->fromArray($data);

        return $object;
    }

    public function setPrototype(ArrayHydrateInterface $prototype): void
    {
        $this->prototype = $prototype;
    }
}
