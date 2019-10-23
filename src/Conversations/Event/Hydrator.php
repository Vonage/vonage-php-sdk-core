<?php

namespace Nexmo\Conversations\Event;

use Nexmo\Conversations\Event\Client as EventClient;

class Hydrator
{
    public function hydrate(array $data)
    {
        $event = new Event();
        $event->createFromArray($data);

        return $event;
    }
}