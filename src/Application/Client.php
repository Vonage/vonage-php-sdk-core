<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Application;

use Nexmo\Client\APIResource;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Entity\CollectionInterface;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Entity\Hydrator\HydratorInterface;
use Nexmo\Entity\IterableServiceShimTrait;

class Client implements ClientAwareInterface, CollectionInterface
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
     * @deprecated Will remove in v3
     */
    protected function getApiResource() : APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUri('/v2/applications')
                ->setCollectionName('applications')
            ;
            $this->api = $api;
        }
        return clone $this->api;
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
        if (!($application instanceof Application)) {
            trigger_error("Passing a Application object to Nexmo\\Application\\Client::get is deprecated, please pass the String ID instead.");
            $application = new Application($application);
        }

        $data = $this->getApiResource()->get($application->getId());
        $application = new Application();
        $application->createFromArray($data);

        return $application;
    }

    /**
     * Creates and saves a new Application
     *
     * @param array|Application Application to save
     */
    public function create($application) : Application
    {
        if (!($application instanceof Application)) {
            trigger_error('Passing an array to Nexmo\Application\Client::create() is deprecated, please pass an Application object instead.');
            $application = $this->createFromArray($application);
        }

        $response = $this->getApiResource()->create($application->toArray());
        $application = $this->hydrator->hydrate($response);
        return $application;
    }

    /**
     * @deprecated Use `create()` instead
     */
    public function post($application) : Application
    {
        trigger_error('Nexmo\Application\Client::post() has been deprecated in favor of the create() method');
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
            trigger_error('Passing an array to Nexmo\Application\Client::update() is deprecated, please pass an Application object instead.');
            $application = $this->createFromArray($application);
        }

        if (is_null($id)) {
            $id = $application->getId();
        } else {
            trigger_error('Passing an ID to Nexmo\Application\Client::update() is deprecated and will be removed in a future release');
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
        trigger_error('Nexmo\Application\Client::put() has been deprecated in favor of the update() method');
        return $this->update($application, $id);
    }

    /**
     * Deletes an application from the Nexmo account
     *
     * @param string|Application Application to delete
     */
    public function delete($application) : bool
    {
        if (($application instanceof Application)) {
            $id = $application->getId();
        } else {
            trigger_error('Passing an ID to Nexmo\Application\Client::delete() is deprecated, please pass an Application object instead');
            $id = $application;
        }

        $this->getApiResource()->delete($id);

        return true;
    }

    /**
     * @deprecated Use Nexmo\Application\Hydrator directly instead
     */
    protected function createFromArray(array $array) : Application
    {
        return $this->hydrator->hydrate($array);
    }
}
