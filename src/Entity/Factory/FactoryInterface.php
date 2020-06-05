<?php
declare(strict_types=1);

namespace Nexmo\Entity\Factory;

interface FactoryInterface
{
    public function create(array $data);
}
