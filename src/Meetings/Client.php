<?php

declare(strict_types=1);

namespace Vonage\Meetings;

use GuzzleHttp\Psr7\MultipartStream;
use Laminas\Diactoros\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception;
use Vonage\Client\Exception\NotFound;
use Vonage\Entity\Filter\KeyValueFilter;
use Vonage\Entity\Hydrator\ArrayHydrator;

class Client implements APIClient
{
    public const IMAGE_TYPES = ['white', 'colored', 'favicon'];

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

    /**
     *
     * Creates a room. Originally this was a string with the display name
     * So there is backwards compatibility cases to cover
     *
     * @param $room string|Room
     *
     * @return Room
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function createRoom(Room|string $room): Room
    {
        if (is_string($room)) {
            trigger_error(
                'Passing a display name string to createRoom is deprecated, please use a Room object',
                E_USER_DEPRECATED
            );
            $roomEntity = new Room();
            $roomEntity->fromArray(['display_name' => $room]);
            $room = $roomEntity;
        }

        $this->api->setBaseUri('/rooms');

        $response = $this->api->create($room->toArray());

        $room = new Room();
        $room->fromArray($response);

        return $room;
    }

    public function updateRoom(string $id, array $payload): Room
    {
        $this->api->setBaseUri('/rooms');

        $response = $this->api->partiallyUpdate($id, $payload);

        $room = new Room();
        $room->fromArray($response);

        return $room;
    }

    public function getAllListedRooms(string $start_id = null, string $end_id = null, int $size = 20): array
    {
        $filterParams = [];

        if ($start_id || $end_id) {
            $start_id ? $filterParams['start_id'] = $start_id : null;
            $end_id ? $filterParams['end_id'] = $end_id : null;
        }
        $response = $this->api->search(
            $filterParams ? new KeyValueFilter($filterParams) : null,
            '/rooms',
        );

        $response->setAutoAdvance(false);
        $response->getApiResource()->setCollectionName('rooms');
        $response->setSize($size);
        $response->setIndex(null);

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
        $response = $this->api->partiallyUpdate($id, $payload);

        $applicationTheme = new ApplicationTheme();
        $applicationTheme->fromArray($response);

        return $applicationTheme;
    }

    public function getRoomsByThemeId(string $themeId, string $startId = null, string $endId = null, int $size = 20): array
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
        $response->setIndex(null);
        $response->setSize($size);

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
        $this->api->update($path, $payload);

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
        $response = $this->api->partiallyUpdate('applications', $payload);

        $application = new Application();
        $application->fromArray($response);

        return $application;
    }

    /**
     * @throws NotFound
     */
    public function returnCorrectUrlEntityFromType(array $uploadUrls, string $type): UrlObject
    {
        foreach ($uploadUrls as $urlObject) {
            if ($urlObject->fields['logoType'] === $type) {
                return $urlObject;
            }
        }

        throw new NotFound('Could not find correct image type');
    }

    /**
     * @throws NotFound
     */
    public function uploadImage(string $themeId, string $type, string $file): bool
    {
        if (!in_array($type, self::IMAGE_TYPES)) {
            throw new \InvalidArgumentException('Image type not recognised');
        }

        $urlEntity = $this->returnCorrectUrlEntityFromType($this->getUploadUrls(), $type);
        $this->uploadToAws($urlEntity, $file);

        $payload = [
            'keys' => [
                $urlEntity->fields['key']
            ]
        ];

        $this->finalizeLogosForTheme($themeId, $payload);

        return true;
    }

    public function uploadToAws(UrlObject $awsUrlObject, string $file): bool
    {
        $stream = new MultipartStream([
            [
                'name' => 'Content-Type',
                'contents' => $awsUrlObject->fields['Content-Type']
            ],
            [
                'name' => 'key',
                'contents' => $awsUrlObject->fields['key']
            ],
            [
                'name' => 'logoType',
                'contents' => $awsUrlObject->fields['logoType']
            ],
            [
                'name' => 'bucket',
                'contents' => $awsUrlObject->fields['bucket']
            ],
            [
                'name' => 'X-Amz-Algorithm',
                'contents' => $awsUrlObject->fields['X-Amz-Algorithm']
            ],
            [
                'name' => 'X-Amz-Credential',
                'contents' => $awsUrlObject->fields['X-Amz-Credential']
            ],
            [
                'name' => 'X-Amz-Date',
                'contents' => $awsUrlObject->fields['X-Amz-Date']
            ],
            [
                'name' => 'X-Amz-Security-Token',
                'contents' => $awsUrlObject->fields['X-Amz-Security-Token']
            ],
            [
                'name' => 'Policy',
                'contents' => $awsUrlObject->fields['Policy']
            ],
            [
                'name' => 'X-Amz-Signature',
                'contents' => $awsUrlObject->fields['X-Amz-Signature']
            ],
            [
                'name' => 'file',
                'contents' => $file
            ]
        ]);

        $awsRequest = new Request($awsUrlObject->url, 'PUT', $stream);
        $awsRequest = $awsRequest->withHeader('Content-Type', 'multipart/form-data');

        $httpClient = $this->api->getClient()->getHttpClient();
        $httpClient->sendRequest($awsRequest);

        return true;
    }
}
