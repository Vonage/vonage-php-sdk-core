<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Application;

use Nexmo\ApiErrorHandler;
use Nexmo\Client\Exception;
use Zend\Diactoros\Request;
use Nexmo\Client\APIResource;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Entity\CollectionInterface;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Entity\IterableAPICollection;
use Nexmo\Entity\ModernCollectionTrait;
use Psr\Http\Message\ResponseInterface;
use Nexmo\Entity\Hydrator\HydratorInterface;

class Client implements ClientAwareInterface, CollectionInterface
{
    use ClientAwareTrait;
    // use ModernCollectionTrait;

    /**
     * @var APIResource
     */
    protected $api;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var IterableAPICollection
     */
    protected $collection = null;

    public function __construct(APIResource $api, HydratorInterface $hydrator)
    {
        $this->api = $api;
        $this->hydrator = $hydrator;
    }

    /**
     * @deprecated Use the hydrator directly
     */
    public function hydrateEntity($data, $id)
    {
        return $this->hydrator->hydrate($data);
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
     * Generates a collection object to help keep API compatibility
     */
    protected function generateCollection($filter = null)
    {
        $this->collection = $this->api->search($filter);
        $this->collection->setHydrator($this->hydrator);
    }

    /**
     * Counts the current search query
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function count() : int
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->count();
    }

    /**
     * Returns the current object in the search
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function current()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->current();
    }

    /**
     * Returns the next object in the search
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function next()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->next();
    }

    /**
     * Returns the key of the current object in the search
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function key()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->key();
    }

    /**
     * Returns if the current iterable is valid
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function valid()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->valid();
    }

    /**
     * Rewinds the current iterable
     * @deprecated This will be removed in a future release, and will be part of a search response
     */
    public function rewind()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->rewind();
    }

    public function setFilter($filter)
    {
        $this->generateCollection($filter);
        return $this;
    }

    public function getFilter()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->getFilter();
    }

    public function getPage()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->getPage();
    }

    public function setPage($index)
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        $this->collection->setPage($index);
        return $this;
    }

    public function getSize()
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        return $this->collection->getSize();
    }

    public function setSize(int $size)
    {
        if (is_null($this->collection)) {
            $this->generateCollection();
        }

        $this->collection->setSize($size);
        return $this;
    }

    /**
     * Returns the specified application
     *    
     */
    public function get($application)
    {
        if (!($application instanceof Application)) {
            trigger_error("Passing a Application object to Nexmo\\Application\\Client::get is deprecated, please pass the String ID instead.");
            $application = new Application($application);
        }

        $data = $this->api->get($application->getId());
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

        $response = $this->api->create($application->toArray());
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

        $data = $this->api->update($id, $application->toArray());
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

        $this->api->delete($id);

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
