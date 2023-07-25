<?php

declare(strict_types=1);

namespace Vonage\Users;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Entity\Filter\EmptyFilter;
use Vonage\Entity\Hydrator\HydratorInterface;
use Vonage\Entity\IterableAPICollection;
use Vonage\Entity\Filter\FilterInterface;

use Vonage\Users\Filter\UserFilter;
use function is_null;

class Client implements ClientAwareInterface, APIClient
{
    use ClientAwareTrait;

    public function __construct(protected APIResource $api, protected ?HydratorInterface $hydrator = null)
    {
    }

    public function getApiResource(): APIResource
    {
        return $this->api;
    }

    public function listUsers(FilterInterface $filter = null): IterableAPICollection
    {
        if (is_null($filter)) {
            $filter = new EmptyFilter();
        } 

        $response = $this->api->search($filter);
        $response->setHydrator($this->hydrator);
        $response->setPageSizeKey('page_size');
        $response->setHasPagination(false);

        return $response;
    }

    public function createUser(User $user): User
    {
        $response = $this->api->create($user->toArray());

        return $this->hydrator->hydrate($response);
    }

    public function getUser(string $id): User
    {
        $response = $this->api->get($id);
        return $this->hydrator->hydrate($response);

        return $returnUser;
    }

    public function updateUser(User $user): User
    {
        if (is_null($user->getId())) {
            throw new \InvalidArgumentException('User must have an ID set');
        }

        $response = $this->api->partiallyUpdate($user->getId(), $user->toArray());

        return $this->hydrator->hydrate($response);
    }

    public function deleteUser(string $id): bool
    {
        try {
            $this->api->delete($id);
            return true;
        } catch (ClientException $exception) {
            return false;
        }
    }
}
