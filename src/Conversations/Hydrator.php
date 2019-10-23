<?php

namespace Nexmo\Conversations;

use Nexmo\Conversations\Event\Client as EventClient;
use Nexmo\Conversations\Member\Client as MemberClient;

class Hydrator
{
    /**
     * @var EventClient
     */
    protected $eventClient;

    /**
     * @var MemberClient
     */
    protected $memberClient;

    public function __construct(EventClient $eventClient, MemberClient $memberClient)
    {
        $this->eventClient = $eventClient;
        $this->memberClient = $memberClient;
    }

    public function hydrate(array $data)
    {
        $conversation = new Conversation();
        $conversation->createFromArray($data);
        $conversation->setEventClient($this->eventClient);
        $conversation->setMemberClient($this->memberClient);

        return $conversation;
    }
}