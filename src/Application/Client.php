<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Application;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareTrait;
use Vonage\Entity\CollectionInterface;
use Vonage\Client\ClientAwareInterface;
use Vonage\Entity\IterableAPICollection;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Entity\IterableServiceShimTrait;
use Vonage\Entity\Hydrator\HydratorInterface;

class Client implements ClientAwareInterface, CollectionInterface, APIClient
{
    use ClientAwareTrait;
    use IterableServiceShimTrait;

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
     * Shim to handle older instatiations of this class
     * Will change in v3 to just return the required API object
     */
    public function getApiResource() : APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUri('/v2/applications')
                ->setCollectionName('applications')
            ;
            $this->api = $api;
        }
        return $this->api;
    }

    /**
     * @deprecated Use an IterableAPICollection object instead
     */
    public static function getCollectionName()
    {
        return 'applications';
    }

    /**
     * @deprecated Use an IterableAPICollection object instead
     */
    public static function getCollectionPath()
    {
        return '/v2/' . self::getCollectionName();
    }

    /**
     * Returns the specified application
     */
    public function get($application)
    {
        if ($application instanceof Application) {
            trigger_error(
                "Passing a Application object to Vonage\\Application\\Client::get is deprecated, please pass the String ID instead.",
                E_USER_DEPRECATED
            );
            $application = $application->getId();
        }

        $data = $this->getApiResource()->get($application);
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
     *
     * @param array|Application Application to save
     */
    public function create($application) : Application
    {
        if (!($application instanceof Application)) {
            trigger_error(
                'Passing an array to Vonage\Application\Client::create() is deprecated, please pass an Application object instead.',
                E_USER_DEPRECATED
            );
            $application = $this->fromArray($application);
        }

        // Avoids a mishap in the API where an ID can be set during creation
        $data = $application->toArray();
        unset($data['id']);

        $response = $this->getApiResource()->create($data);
        $application = $this->hydrator->hydrate($response);
        return $application;
    }

    /**
     * @deprecated Use `create()` instead
     */
    public function post($application) : Application
    {
        trigger_error(
            'Vonage\Application\Client::post() has been deprecated in favor of the create() method',
            E_USER_DEPRECATED
        );
        return $this->create($application);
    }

    /**
     * Saves an existing application
     *
     * @param array|Application $application
     */
    public function update($application, $id = null) : Application
    {
        if (!($application instanceof Application)) {
            trigger_error(
                'Passing an array to Vonage\Application\Client::update() is deprecated, please pass an Application object instead.',
                E_USER_DEPRECATED
            );
            $application = $this->fromArray($application);
        }

        if (is_null($id)) {
            $id = $application->getId();
        } else {
            trigger_error(
                'Passing an ID to Vonage\Application\Client::update() is deprecated and will be removed in a future release',
                E_USER_DEPRECATED
            );
        }

        $data = $this->getApiResource()->update($id, $application->toArray());
        $application = $this->hydrator->hydrate($data);

        return $application;
    }

    /**
     * @deprecated
     */
    public function put($application, $id = null) : Application
    {
        trigger_error(
            'Vonage\Application\Client::put() has been deprecated in favor of the update() method',
            E_USER_DEPRECATED
        );
        return $this->update($application, $id);
    }

    /**
     * Deletes an application from the Vonage account
     *
     * @param string|Application Application to delete
     */
    public function delete($application) : bool
    {
        if ($application instanceof Application) {
            trigger_error(
                'Passing an Application to Vonage\Application\Client::delete() is deprecated, please pass a string ID instead',
                E_USER_DEPRECATED
            );
            $id = $application->getId();
        } else {
            $id = $application;
        }

        $this->getApiResource()->delete($id);

        return true;
    }

    /**
     * @deprecated Use Vonage\Application\Hydrator directly instead
     */
    protected function fromArray(array $array) : Application
    {
        return $this->hydrator->hydrate($array);
    }
}
