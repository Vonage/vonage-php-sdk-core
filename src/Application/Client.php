<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Application;

use Nexmo\Client\APIClient;
use Nexmo\Client\APIResource;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Entity\IterableAPICollection;
use Nexmo\Entity\Hydrator\ArrayHydrator;
use Nexmo\Entity\Hydrator\HydratorInterface;

class Client implements APIClient
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

    public function __construct(APIResource $api, HydratorInterface $hydrator)
    {
        $this->api = $api;
        $this->hydrator = $hydrator;
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    /**
     * Returns the specified application
     */
    public function get(string $id) : Application
    {
        $data = $this->api->get($id);

        $application = new Application();
        $application->fromArray($data);

        return $application;
    }

    public function getAll() : IterableAPICollection
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
     */
    public function create(Application $application) : Application
    {
        $response = $this->api->create($application->toArray());

        $application = $this->hydrator->hydrate($response);
        return $application;
    }

    /**
     * Saves an existing application
     */
    public function update(Application $application) : Application
    {
        $data = $this->api->update($application->getId(), $application->toArray());
        $application = $this->hydrator->hydrate($data);

        return $application;
    }

    /**
     * Deletes an application from the Nexmo account
     */
    public function delete(Application $application) : bool
    {
        $this->api->delete($application->getId());
        return true;
    }
}
