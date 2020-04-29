<?php

namespace Nexmo\Entity\Hydrator;

class ArrayHydrator implements PrototypeHydrator
{
    /**
     * @var ArrayHydratorInterface
     */
    protected $prototype;

    public function hydrate(array $data)
    {
        $object = clone $this->prototype;
        return $this->hydrateObject($data, $object);
    }

    public function hydrateObject(array $data, $object)
    {
        $object->fromArray($data);
        return $object;
    }

    public function setPrototype($prototype)
    {
        $this->prototype = $prototype;
    }
}
