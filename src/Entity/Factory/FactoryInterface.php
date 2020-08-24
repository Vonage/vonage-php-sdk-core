<?php
declare(strict_types=1);

namespace Vonage\Entity\Factory;

interface FactoryInterface
{
    public function create(array $data);
}
