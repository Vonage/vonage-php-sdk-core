<?php

namespace Nexmo\Call;

use Nexmo\Client;
use Nexmo\Entity\Hydrator\HydratorInterface;

class Hydrator implements HydratorInterface
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Hydrate an object that the hydrator creates
     */
    public function hydrate(array $data) : Call
    {
        $call = new Call();

        return $this->hydrateObject($data, $call);
    }

    /**
     * Hydrate an existing object created outside of the hydrator
     */
    public function hydrateObject(array $data, $object) : Call
    {
        $object->createFromArray($data);
        $object->setClient($this->client);

        return $object;
    }
}
