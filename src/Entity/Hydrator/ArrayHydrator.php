<?php

namespace Vonage\Entity\Hydrator;

class ArrayHydrator implements HydratorInterface
{
    /**
     * @var ArrayHydratorInterface
     */
    protected $prototype;

    public function hydrate(array $data)
    {
        $object = clone $this->prototype;
        $object->fromArray($data);

        return $object;
    }

    public function hydrateObject(array $data, $object)
    {
        $object->fromArray($data);
        return $object;
    }

    public function setPrototype(ArrayHydrateInterface $prototype)
    {
        $this->prototype = $prototype;
    }
}
