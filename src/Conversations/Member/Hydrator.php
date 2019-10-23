<?php

namespace Nexmo\Conversations\Member;

class Hydrator
{
    public function hydrate(array $data)
    {
        $member = new Member();
        $member->createFromArray($data);

        return $member;
    }
}