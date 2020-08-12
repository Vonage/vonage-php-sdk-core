<?php
declare(strict_types=1);

namespace Vonage\Numbers;

use Vonage\Entity\Hydrator\HydratorInterface;
use Vonage\Numbers\Number;

class Hydrator implements HydratorInterface
{
    public function hydrate(array $data)
    {
        $number = new Number();
        return $this->hydrateObject($data, $number);
    }

    public function hydrateObject(array $data, $object)
    {
        $object->fromArray($data);
        return $object;
    }
}
