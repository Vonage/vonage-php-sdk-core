<?php

declare(strict_types=1);

namespace Vonage\Entity\Hydrator;

class ConstructorHydrator implements HydratorInterface
{
    protected string $prototype;

    public function hydrate(array $data): mixed
    {
        $className = $this->prototype;
        return new $className($data);
    }

    public function hydrateObject(array $data, $object): never
    {
        throw new \RuntimeException('Constructor Hydration can not happen on an existing object');
    }

    public function setPrototype(string $prototype): void
    {
        $this->prototype = $prototype;
    }
}
