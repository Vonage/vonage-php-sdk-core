<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Application;

use Nexmo\Entity\Collection;
use Nexmo\Client\OpenAPIResource;
use Nexmo\Entity\FilterInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Entity\Hydrator\HydratorInterface;

class Client implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @var OpenAPIResource
     */
    protected $api;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    public function __construct(OpenAPIResource $api, HydratorInterface $hydrator)
    {
        $this->api = $api;
        $this->hydrator = $hydrator;
    }

    public function get(string $id) : Application
    {
        $data = $this->api->get($id);

        $application = new Application();
        $application->createFromArray($data);

        return $application;
    }

    public function create(Application $application) : Application
    {
        $response = $this->api->create($application->toArray());
        $application = $this->hydrator->hydrate($response);

        return $application;
    }

    public function update(Application $application) : Application
    {
        $id = $application->getId();
        if (is_null($id)) {
            throw new \RuntimeException('Cannot update an application without an ID.');
        }

        $data = $this->api->update($application->getId(), $application->toArray());
        $application = $this->hydrator->hydrate($data);

        return $application;
    }

    public function delete(Application $application) : void
    {
        $this->api->delete($application->getId());
    }

    public function search(FilterInterface $filter = null) : Collection
    {
        $collection = $this->api->search($filter);
        $collection->setHydrator($this->hydrator);

        return $collection;
    }
}
