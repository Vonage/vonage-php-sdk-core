<?php

namespace Nexmo\Call;

use Closure;
use Nexmo\Conversations\Client;
use Nexmo\Entity\HydratorInterface;

class Hydrator implements HydratorInterface
{
    /**
     * @var Client
     */
    protected $conversationClient;

    public function __construct(Client $client)
    {
        $this->conversationClient = $client;
    }

    public function hydrate(array $data)
    {
        $call = new Call();
        $call->createFromArray($data);

        $conversationClient = $this->conversationClient;
        $conversationClosure = function () use ($conversationClient) {
            return $conversationClient->get($this->data['conversation_uuid']);
        };
        $reboundConversationClosure = Closure::bind($conversationClosure, $call, Call::class);

        $call->setConversation($reboundConversationClosure);

        return $call;
    }
}