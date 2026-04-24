<?php

declare(strict_types=1);

namespace Vonage\Application;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\Hydrator\HydratorInterface;
use Vonage\Entity\IterableAPICollection;

use function is_null;

class Client
{
    public function __construct(protected APIResource $api, protected ?HydratorInterface $hydrator = null)
    {
        if (is_null($this->hydrator)) {
            $this->hydrator = new Hydrator();
        }
    }

    /**
     * Returns the specified application
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function get(string $application): Application
    {
        $data = $this->api->get($application);
        $application = new Application();
        $application->fromArray($data);

        return $application;
    }

    public function getAll(): IterableAPICollection
    {
        $response = $this->api->search();
        $response->setApiResource(clone $this->api);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Application());

        $response->setHydrator($hydrator);
        return $response;
    }

    /**
     * Creates and saves a new Application
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws Exception
     */
    public function create(Application $application): Application
    {
        // Avoids a mishap in the API where an ID can be set during creation
        $data = $application->toArray();
        unset($data['id']);

        $response = $this->api->create($data);

        return $this->hydrator->hydrate($response);
    }

    /**
     * Saves an existing application
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws Exception
     */
    public function update(Application $application): Application
    {
        $data = $this->api->update($application->getId(), $application->toArray());
        return $this->hydrator->hydrate($data);
    }

    /**
     * Deletes an application from the Vonage account
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function delete(string $application): bool
    {
        $this->api->delete($application);

        return true;
    }
}
