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

    public function getRoom(string $id): Room
    {
        $this->api->setBaseUri('/rooms/');
        $response = $this->api->get($id);

        $room = new Room();
        $room->fromArray($response);

        return $room;
    }

    public function createRoom(string $displayName): Room
    {
        $this->api->setBaseUri('/rooms/');

        $response = $this->api->create([
            'display_name' => $displayName
        ]);

        $room = new Room();
        $room->fromArray($response);

        return $room;
    }

    public function updateRoom(string $id, array $payload): Room
    {
        $response = $this->api->patch($id, $payload);

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

    public function getRecording(string $id): ?Recording
    {
        $this->api->setBaseUri('/recordings/');
        $response = $this->api->get($id);

        $recording = new Recording();
        $recording->fromArray($response);

        return $recording;
    }

    public function deleteRecording(string $id): bool
    {
        $this->api->setBaseUri('/recordings/');
        $this->api->delete($id);

        return true;
    }

    public function getRecordingsFromSession(string $sessionId): IterableAPICollection
    {
        $response = $this->api->search(null, '/sessions/' . $sessionId . '/recordings');
        $response->setNaiveCount(true);
        $response->getApiResource()->setCollectionName('recordings');

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Recording());

        $response->setHydrator($hydrator);

        return $response;
    }

    public function getDialInNumbers(): IterableAPICollection
    {
        $this->api->setBaseUri('/dial-in-numbers');
        $this->api->setIsHAL(false);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new DialInNumber());

        $response->setHydrator($hydrator);

        return $response;
    }
}
