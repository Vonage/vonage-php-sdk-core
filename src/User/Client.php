<?php

namespace Nexmo\User;

use Nexmo\Client\OpenAPIResource;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Entity\HydratorInterface;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Entity\Collection;
use Nexmo\Entity\FilterInterface;

class Client implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @var OpenAPIResource
     */
    protected $api;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    public function __construct(OpenAPIResource $api, HydratorInterface $hydrator)
    {
        $this->api = $api;
        $this->hydrator = $hydrator;
    }

    public function create(User $user) : User
    {
        $data = $user->toArray();
        $response = $this->api->create($data);
        
        return $this->hydrator->hydrate($response);
    }

    /**
     * @deprecated See list()
     */
    public function fetch() : Collection
    {
        return $this->list();
    }

    public function get(string $id) : User
    {
        $response = $this->api->get($id);
        return $this->hydrator->hydrate($response);
    }
    
    /**
     * @deprecated See create()
     */
    public function post(User $user) : User
    {
        return $this->create($user);
    }

    public function list() : Collection
    {
        return $this->api->search();
    }
}
