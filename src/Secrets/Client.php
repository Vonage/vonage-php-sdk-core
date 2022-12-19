<?php

namespace Vonage\Secrets;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\IterableAPICollection;

class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api)
    {
        $this->api = $api;
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function get(string $accountId, string $id): Secret
    {
        $data = $this->api->get("{$accountId}/secrets/{$id}");

        return new Secret($data);
    }

    public function list(string $accountId): IterableAPICollection
    {
        $collection = $this->api->search(null, "/accounts/{$accountId}/secrets");
        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Secret());
        $collection->setHydrator($hydrator);

        return $collection;
    }

    public function create(string $accountId, string $secret): Secret
    {
        $response = $this->api->create(['secret' => $secret], "/{$accountId}/secrets");
        return new Secret($response);
    }

    public function revoke(string $accountId, string $id)
    {
        $this->api->delete("{$accountId}/secrets/{$id}");
    }
}
