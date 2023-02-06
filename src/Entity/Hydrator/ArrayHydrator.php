<?php

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
