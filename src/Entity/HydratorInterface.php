<?php

namespace Nexmo\Entity;

interface HydratorInterface
{
    public function hydrate(array $data);
}