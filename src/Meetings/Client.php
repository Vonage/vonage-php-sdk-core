<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Meetings;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\IterableAPICollection;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function createRoom(string $displayName): Room
    {
        $response = $this->api->create([
            'display_name' => $displayName
        ]);

        $room = new Room();
        $room->fromArray($response);

        return $room;
    }

    public function getAllAvailableRooms(): IterableAPICollection
    {
        $response = $this->api->search(null, '/rooms');
        $response->setNaiveCount(true);
        $response->getApiResource()->setCollectionName('rooms');

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Room());

        $response->setHydrator($hydrator);

        return $response;
    }
}
