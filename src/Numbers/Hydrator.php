<?php
declare(strict_types=1);

namespace Nexmo\Numbers;

use Nexmo\Entity\Hydrator\HydratorInterface;
use Nexmo\Numbers\Number;

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
