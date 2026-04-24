<?php

declare(strict_types=1);

namespace Vonage\Verify;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;
use Vonage\Entity\Filter\KeyValueFilter;
use Vonage\Entity\IterableAPICollection;

class Client
{
    public function __construct(protected APIResource $api)
    {
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function startVerification(StartVerification $request): string
    {
        $response = $this->api->create($request->toArray(), '/json');

        return $response['request_id'];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function startPsd2Verification(StartPSD2 $request): string
    {
        $response = $this->api->create($request->toArray(), '/psd2/json');

        return $response['request_id'];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function get(string $requestId): Verification
    {
        $data = $this->api->get('search/json', ['request_id' => $requestId]);

        if (($data['request_id'] ?? null) !== $requestId) {
            throw new ClientException\Request(
                $data['error_text'] ?? 'Unexpected response from Verify search'
            );
        }

        return Verification::fromArray($data);
    }

    /**
     * @param string[] $requestIds
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function search(array $requestIds): IterableAPICollection
    {
        $api = clone $this->api;
        $api->setCollectionName('verification_requests');

        $response = $api->search(
            new KeyValueFilter(['request_ids' => $requestIds]),
            $this->api->getBaseUri() . '/search/json'
        );

        $response->setHasPagination(false);
        $response->setAutoAdvance(false);
        $response->setHydrator(new class {
            public function hydrate(array $data): Verification
            {
                return Verification::fromArray($data);
            }
        });

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function check(string $requestId, string $code): Check
    {
        $data = $this->api->create([
            'request_id' => $requestId,
            'code' => $code,
        ], '/check/json');

        return Check::fromArray($data);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function cancel(string $requestId): bool
    {
        $data = $this->api->create(
            ['request_id' => $requestId, 'cmd' => 'cancel'],
            '/control/json'
        );

        return ($data['status'] ?? '') === '0';
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function triggerNextEvent(string $requestId): bool
    {
        $data = $this->api->create(
            ['request_id' => $requestId, 'cmd' => 'trigger_next_event'],
            '/control/json'
        );

        return ($data['status'] ?? '') === '0';
    }
}


