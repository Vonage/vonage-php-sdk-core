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
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\Hydrator\HydratorInterface;
use Vonage\Entity\IterableAPICollection;

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

    public function listUsers($pageSize = null, $order = null, $cursor = null): IterableAPICollection
    {
        if (is_null($pageSize) && is_null($order) && is_null($cursor)) {
            $filter = new EmptyFilter();
        } else {
            $filter = new UserFilter();
            $filter->setPageSize($pageSize);
            $filter->setOrder($order);
            $filter->setCursor($cursor);
        }

        $response = $this->api->search($filter);
        $response->setHydrator($this->hydrator);
        $response->setSize($pageSize);
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
        $returnUser = new User();
        $returnUser->fromArray($response);

        return $returnUser;
    }

    public function updateUser(User $user): User
    {
        if (!$user->getId()) {
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
