<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

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

    /**
     * @var APIResource
     */
    protected $api;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    public function __construct(APIResource $api = null, HydratorInterface $hydrator = null)
    {
        $this->api = $api;
        $this->hydrator = $hydrator;

        // Shim to handle BC with old constructor
        // Will remove in v3
        if (is_null($this->hydrator)) {
            $this->hydrator = new Hydrator();
        }
    }

    /**
     * Shim to handle older instantiations of this class
     * Will change in v3 to just return the required API object
     */
    public function getApiResource(): APIResource
    {
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
     * @deprecated Use an IterableAPICollection object instead
     */
    public static function getCollectionName(): string
    {
        return 'applications';
    }

    /**
     * @deprecated Use an IterableAPICollection object instead
     */
    public static function getCollectionPath(): string
    {
        return '/v2/' . self::getCollectionName();
    }

    /**
     * Returns the specified application
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function get(string $application): Application
    {
        $data = $this->getApiResource()->get($application);
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

        $response = $this->getApiResource()->create($data);

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

        $data = $this->getApiResource()->update($id, $application->toArray());
        $application = $this->hydrator->hydrate($data);

        return $application;
    }

    /**
     * Deletes an application from the Vonage account
     *
     * @throws ClientExceptionInterface
     * @throws ClientException
     */
    public function delete(string $application): bool
    {
        $this->getApiResource()->delete($application);

        return true;
    }

    /**
     * @throws Exception
     *
     * @deprecated Use Vonage\Application\Hydrator directly instead
     */
    protected function fromArray(array $array): Application
    {
        return $this->hydrator->hydrate($array);
    }
}
