<?php

declare(strict_types=1);

namespace Vonage\Users;

use Vonage\Entity\Hydrator\HydratorInterface;

class Hydrator implements HydratorInterface
{
    public function hydrate(array $data)
    {
        $user = new User();
        return $this->hydrateObject($data, $user);
    }

    public function hydrateObject(array $data, $object)
    {
        $object->fromArray($data);
        return $object;
    }
}
