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

    /**
     * @param array $data
     *
     * @return ArrayHydrateInterface
     */
    public function hydrate(array $data): ArrayHydrateInterface
    {
        $object = clone $this->prototype;
        $object->fromArray($data);

        return $object;
    }

    /**
     * @param array $data
     * @param $object
     *
     * @return mixed
     */
    public function hydrateObject(array $data, $object)
    {
        $object->fromArray($data);

        return $object;
    }

    /**
     * @param ArrayHydrateInterface $prototype
     */
    public function setPrototype(ArrayHydrateInterface $prototype): void
    {
        $this->prototype = $prototype;
    }
}
