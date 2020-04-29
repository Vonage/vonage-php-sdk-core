<?php
declare(strict_types=1);

namespace Nexmo\Entity\Hydrator;

interface PrototypeHydrator extends HydratorInterface
{
    public function setPrototype($prototype);
}