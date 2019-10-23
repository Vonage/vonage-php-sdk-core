<?php

namespace Nexmo\User;

use Nexmo\Entity\HydratorInterface;

class Hydrator implements HydratorInterface
{
    public function hydrate(array $data)
    {
        $user = new User();
        $user->createFromArray($data);

        return $user;
    }
}