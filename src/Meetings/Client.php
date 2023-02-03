<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Meetings;

use Laminas\Diactoros\Request;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Entity\Filter\KeyValueFilter;
use Vonage\Entity\Hydrator\ArrayHydrator;

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

    public function getAllAvailableRooms(string $start_id = null, string $end_id = null): array
    {
        if ($start_id || $end_id) {
            $filterParams = [];
            $start_id ? $filterParams['start_id'] = $start_id : null;
            $end_id ? $filterParams['end_id'] = $end_id : null;

            if ($filterParams) {
                $response = $this->api->search(new KeyValueFilter($filterParams), '/rooms');
            }
        } else {
            $response = $this->api->search(null, '/rooms');
        }

        $response->setAutoAdvance(false);
        $response->getApiResource()->setCollectionName('rooms');

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Room());

        $response->setHydrator($hydrator);

        // Currently have to do this until we can extend off the Iterator to handle Meetings data structures
        $roomPayload = [];

        foreach ($response as $room) {
            $roomPayload[] = $room;
        }

        return $roomPayload;
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

    public function deleteTheme(string $id, bool $force = false): bool
    {
        $this->api->setBaseUri('/themes');

        if ($force) {
            $id .= '?force=true';
        }

        $this->api->delete($id);

        return true;
    }

    public function updateTheme(string $id, array $payload): ?ApplicationTheme
    {
        $this->api->setBaseUri('/themes');
        $response = $this->api->patch($id, $payload);

        $applicationTheme = new ApplicationTheme();
        $applicationTheme->fromArray($response);

        return $applicationTheme;
    }

    public function getRoomsByThemeId(string $themeId, string $startId = null, string $endId = null): array
    {
        $this->api->setIsHAL(true);
        $this->api->setCollectionName('rooms');

        if ($startId || $endId) {
            $filterParams = [];
            $startId ? $filterParams['start_id'] = $startId : null;
            $endId ? $filterParams['end_id'] = $endId : null;

            if ($filterParams) {
                $response = $this->api->search(new KeyValueFilter($filterParams), '/themes/' . $themeId . '/rooms');
            }
        } else {
            $response = $this->api->search(null, '/themes/' . $themeId . '/rooms');
        }

        $response->setAutoAdvance(false);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Room());

        $response->setHydrator($hydrator);

        // Currently have to do this until we can extend off the Iterator to handle Meetings data structures
        $roomPayload = [];

        foreach ($response as $room) {
            $roomPayload[] = $room;
        }

        return $roomPayload;
    }

    public function finalizeLogosForTheme(string $themeId, array $payload): bool
    {
        $path = $themeId . '/finalizeLogos';
        $this->api->setBaseUri('/themes');
        $response = $this->api->update($path, $payload);

        return true;
    }

    public function getUploadUrls(): array
    {
        $response = [];

        $awsUploadObjects = $this->api->get('themes/logos-upload-urls');

        foreach ($awsUploadObjects as $routeObject) {
            $returnObject = new UrlObject();
            $returnObject->fromArray($routeObject);
            $response[] = $returnObject;
        }

        return $response;
    }

    public function updateApplication(array $payload): ?Application
    {
        $response = $this->api->patch('applications', $payload);

        $application = new Application();
        $application->fromArray($response);

        return $application;
    }

    public function uploadImage(string $themeId, $file): bool
    {
        $getUrlsResponse = $this->getUploadUrls();

        // We get the first entry, at this point not clear what the correct behaviour is
        $urlEntity = $getUrlsResponse[0];

        // Then we upload it to AWS
        $this->uploadToAws($urlEntity, $file);

        // Then we hit finalize logos

        $payload = [
            'keys' => [
                $urlEntity->fields['key']
            ]
        ];

        $this->finalizeLogosForTheme($themeId, $payload);

        return true;
    }

    public function uploadToAws(UrlObject $awsUrlObject, $file): bool
    {
        $request = new Request($awsUrlObject->url, 'PUT');

        $httpClient = $this->api->getClient()->getHttpClient();
        $response = $httpClient->sendRequest($request);

        return true;
    }
}
