<?php
declare(strict_types=1);

namespace Nexmo\Account;

use Nexmo\Entity\Hydrator\HydratorInterface;

class PriceHydrator implements HydratorInterface
{
    public function hydrate(array $data)
    {
        throw new \RuntimeException(
            'Cannot hydrator with a default object, use hydrateObject() or PriceFactory::build()'
        );
    }

    public function hydrateObject(array $data, $object) : Price
    {
        $object->fromArray($data);
        return $object;
    }
}
