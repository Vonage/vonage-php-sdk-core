<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Users;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Entity\Filter\EmptyFilter;
use Vonage\Entity\Filter\KeyValueFilter;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\Hydrator\HydratorInterface;
use Vonage\Entity\IterableAPICollection;

use function is_null;

class Client implements ClientAwareInterface, APIClient
{
    use ClientAwareTrait;

    public function __construct(protected APIResource $api, protected ?HydratorInterface $hydrator = null)
    {
    }

    public function getApiResource(): APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUri('/v1/users')
                ->setCollectionName('users');
            $this->api = $api;
        }

        return $this->api;
    }

    public function getUsers($pageSize = null, $order = null, $cursor = null): IterableAPICollection
    {
        if (is_null($pageSize) && is_null($order) && is_null($cursor)) {
            $filter = new EmptyFilter();
        }

        if ($pageSize || $order || $cursor) {
            $filter = new KeyValueFilter([
                'order' => $order,
                'cursor' => $cursor
            ]);
        }

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new User());

        $response = $this->api->search($filter);
        $response->setHydrator($hydrator);
        $response->setSize($pageSize);
        $response->setPageSizeKey('page_size');
        $response->setHasPagination(false);

        return $response;
    }

    public function createUser(User $user): User
    {
        $response = $this->api->create($user->toArray());
        $userObject = new User();
        $userObject->fromArray($response);

        return $userObject;
    }

    public function getUserById(string $id): User
    {
        $response = $this->api->get($id);
        $returnUser = new User();
        $returnUser->fromArray($response);

        return $returnUser;
    }

    public function updateUser(User $user, string $id): User
    {
        $response = $this->api->partiallyUpdate($id, $user->toArray());
        $returnUser = new User();
        $returnUser->fromArray($response);

        return $returnUser;
    }

    public function deleteUserById(string $id): bool
    {
        try {
            $this->api->delete($id);
            return true;
        } catch (ClientException $exception) {
            return false;
        }
    }
}
