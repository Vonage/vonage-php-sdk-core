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
        $this->api->setBaseUri('/rooms');
        $response = $this->api->get($id);

        $room = new Room();
        $room->fromArray($response);

        return $room;
    }

    public function createRoom(string $displayName): Room
    {
        $this->api->setBaseUri('/rooms');

        $response = $this->api->create([
            'display_name' => $displayName
        ]);

        $room = new Room();
        $room->fromArray($response);

        return $room;
    }

    public function updateRoom(string $id, array $payload): Room
    {
        $this->api->setBaseUri('/rooms');

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
        $this->api->setBaseUri('/recordings');
        $response = $this->api->get($id);

        $recording = new Recording();
        $recording->fromArray($response);

        return $recording;
    }

    public function deleteRecording(string $id): bool
    {
        $this->api->setBaseUri('/recordings');
        $this->api->delete($id);

        return true;
    }

    public function getRecordingsFromSession(string $sessionId): array
    {
        $response = $this->api->get('sessions/' . $sessionId . '/recordings');
        $recordings = [];

        foreach ($response as $recording) {
            $recordingEntity = new Recording();
            $recordingEntity->fromArray($recording);
            $recordings[] = $recordingEntity;
        }

        return $recordings;
    }

    public function getDialInNumbers(): array
    {
        $response = $this->api->get('dial-in-numbers');
        $numbers = [];

        foreach ($response as $dialInNumber) {
            $dialInEntity = new DialInNumber();
            $dialInEntity->fromArray($dialInNumber);
            $numbers[] = $dialInEntity;
        }

        return $numbers;
    }

    public function getApplicationThemes(): array
    {
        $response = $this->api->get('themes');

        $themes = [];

        foreach ($response as $applicationTheme) {
            $themeEntity = new ApplicationTheme();
            $themeEntity->fromArray($applicationTheme);
            $themes[] = $themeEntity;
        }
        return $themes;
    }

    public function createApplicationTheme(string $name): ?ApplicationTheme
    {
        $this->api->setBaseUri('/themes');

        $response = $this->api->create([
            'theme_name' => $name
        ]);

        $applicationTheme = new ApplicationTheme();
        $applicationTheme->fromArray($response);

        return $applicationTheme;
    }

    public function getThemeById(string $id): ?ApplicationTheme
    {
        $this->api->setBaseUri('/themes');
        $response = $this->api->get($id);

        $applicationTheme = new ApplicationTheme();
        $applicationTheme->fromArray($response);

        return $applicationTheme;
    }

    public function deleteTheme(string $id): bool
    {
        $this->api->setBaseUri('/themes');
        $this->api->delete($id);

        return true;
    }

    public function updateTheme(string $id, array $payload): ?ApplicationTheme
    {
        $this->api->setBaseUri('/themes/');
        $response = $this->api->patch($id, $payload);

        $applicationTheme = new ApplicationTheme();
        $applicationTheme->fromArray($response);

        return $applicationTheme;
    }

    public function getRoomsByThemeId(string $themeId): IterableAPICollection
    {
        $this->api->setIsHAL(true);
        $this->api->setCollectionName('rooms');
        $response = $this->api->search(null, '/themes/' . $themeId . '/rooms');

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Room());

        $response->setHydrator($hydrator);

        return $response;
    }
}
