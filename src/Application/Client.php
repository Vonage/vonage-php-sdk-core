<?php

declare(strict_types=1);

namespace Vonage\Application;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception\Exception as ClientException;
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

    /**
     * @deprecated This method will be removed in the next major version.
     *             The APIResource is injected and should not be accessed directly from outside the client.
     */
    public function getApiResource(): APIResource
    {
        trigger_error(
            'Vonage\\Application\\Client::getApiResource() is deprecated and will be removed in the next major version.',
            E_USER_DEPRECATED
        );
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUri('/v2/applications')
                ->setCollectionName('applications');
            $this->api = $api;
        }

        return $this->api;
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
    public function update($application, ?string $id = null): Application
    {
        if (!($application instanceof Application)) {
            trigger_error(
                'Passing an array to Vonage\Application\Client::update() is deprecated, ' .
                'please pass an Application object instead.',
                E_USER_DEPRECATED
            );

            $application = $this->fromArray($application);
        }

        if (is_null($id)) {
            $id = $application->getId();
        } else {
            trigger_error(
                'Passing an ID to Vonage\Application\Client::update() is deprecated ' .
                'and will be removed in a future release',
                E_USER_DEPRECATED
            );
        }

        $data = $this->api->update($id, $application->toArray());
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
