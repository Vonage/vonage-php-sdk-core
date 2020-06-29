<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Numbers;

use Nexmo\Client\APIClient;
use Nexmo\Client\APIResource;
use Nexmo\Entity\Filter\FilterInterface;
use Nexmo\Entity\IterableAPICollection;
use Nexmo\Numbers\Filter\AvailableNumbers;
use Nexmo\Numbers\Filter\OwnedNumbers;

class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api = null)
    {
        $this->api = $api;
    }

    public function getAPIResource() : APIResource
    {
        return $this->api;
    }

    public function update(Number $number) : Number
    {
        if (is_null($number->getCountry())) {
            throw new \InvalidArgumentException("Number is missing required country code");
        }

        $this->api->submit($number->toArray(), '/number/update');
        return $number;
    }

    /**
     * Returns a number
     */
    public function get(string $number) : Number
    {
        $filter = new OwnedNumbers(['pattern' => $number]);
        $items =  $this->searchOwned($filter);

        return $items->current();
    }

    /**
     * Returns a set of numbers for the specified country
     */
    public function searchAvailable(string $country, FilterInterface $options = null) : IterableAPICollection
    {
        if (is_null($options)) {
            $options = new AvailableNumbers();
        }

        $api = $this->getApiResource();
        $api->setCollectionName('numbers');

        $response = $api->search(
            new AvailableNumbers($options->getQuery() + ['country' => $country]),
            '/number/search'
        );
        $response->setHydrator(new Hydrator());
        $response->setAutoAdvance(false); // The search results on this can be quite large

        return $response;
    }

    /**
     * Returns a set of numbers for the specified country
     */
    public function searchOwned(FilterInterface $options = null) : IterableAPICollection
    {
        $api = $this->getApiResource();
        $api->setCollectionName('numbers');

        $response = $api->search($options, '/account/numbers');
        $response->setHydrator(new Hydrator());
        $response->setAutoAdvance(false); // The search results on this can be quite large

        return $response;
    }

    public function purchase(string $number, string $country) : void
    {
        $body = [
            'msisdn' => $number,
            'country' => $country
        ];

        $this->api->submit($body, '/number/buy');
    }

    public function cancel(string $number, string $country) : void
    {
        $body = [
            'msisdn' => $number,
            'country' => $country
        ];

        $this->api->submit($body, '/number/cancel');
    }
}
