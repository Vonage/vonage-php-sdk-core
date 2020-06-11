<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Numbers;

use Nexmo\Client\APIClient;
use Nexmo\Client\Exception;
use Nexmo\Client\APIResource;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\Exception\ThrottleException;
use Nexmo\Entity\Filter\FilterInterface;
use Nexmo\Entity\Filter\KeyValueFilter;
use Nexmo\Entity\IterableAPICollection;
use Nexmo\Numbers\Filter\AvailableNumbers;
use Nexmo\Numbers\Filter\OwnedNumbers;

class Client implements ClientAwareInterface, APIClient
{
    /**
     * @deprecated This client no longer needs to be ClientAware
     */
    use ClientAwareTrait;

    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api = null)
    {
        $this->api = $api;
    }

    /**
     * @param Number $number Number to update
     */
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
     *
     * @param Number|string $number Number to fetch, deprecating passing a `Number` object
     */
    public function get($number) : Number
    {
        $items =  $this->searchOwned($number);

        // This is legacy behaviour, so we need to keep it even though
        // it isn't technically the correct message
        if (count($items) != 1) {
            throw new Exception\Request('number not found', 404);
        }

        return $items[0];
    }

    /**
     * Returns a set of numbers for the specified country
     *
     * @param FilterInterface $options Additional options, see https://developer.nexmo.com/api/numbers#getAvailableNumbers
     */
    public function searchAvailable(string $country, FilterInterface $options = null) : array
    {
        if (is_array($options)) {
            if (!empty($options)) {
                trigger_error(
                    'Passing an array to ' . get_class($this) . '::searchAvailable() is deprecated, pass a FilterInterface instead',
                    E_USER_DEPRECATED
                );
            }
        }

        if ($options) {
            // These are all optional parameters
            $possibleParameters = [
                'country' => 'string',
                'pattern' => 'string',
                'search_pattern' => 'integer',
                'features' => 'array',
                'size' => 'integer',
                'type' => 'string',
                'index' => 'integer'
            ];

            $options = $this->parseParameters($possibleParameters, $option);
            $options = new AvailableNumbers($options);
        } else {
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
        $query = $this->parseParameters($possibleParameters, $options);

        return $this->handleNumberSearchResult($collection, null);
    }

    /**
     * Returns a set of numbers for the specified country
     *
     * @param string|Number $number Number to search for, if any
     * @param array $options Additional options, see https://developer.nexmo.com/api/numbers#getOwnedNumbers
     */
    public function searchOwned(FilterInterface $options = null) : array
    {
        if (is_null($options)) {
            $options = new OwnedNumbers();
        }

        // These are all optional parameters
        $possibleParameters = [
            'pattern' => 'string',
            'search_pattern' => 'integer',
            'size' => 'integer',
            'index' => 'integer',
            'has_application' => 'boolean',
            'application_id' => 'string'
        ];

        $options = $this->parseParameters($possibleParameters, $options->getQuery());
        $options = new OwnedNumbers($options);
        $api = $this->getApiResource();
        $api->setCollectionName('numbers');

        $response = $api->search($options, '/account/numbers');
        $response->setHydrator(new Hydrator());
        $response->setAutoAdvance(false); // The search results on this can be quite large

        return $this->handleNumberSearchResult($collection, $number);
    }

    /**
     * Checks and converts parameters into appropriate values for the API
     */
    protected function parseParameters(array $possibleParameters, array $data = []) : array
    {
        $query = [];
        foreach ($data as $param => $value) {
            if (!array_key_exists($param, $possibleParameters)) {
                throw new Exception\Request("Unknown option: '" . $param . "'");
            }

            switch ($possibleParameters[$param]) {
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if (is_null($value)) {
                        throw new Exception\Request("Invalid value: '" . $param . "' must be a boolean value");
                    }
                    $value = $value ? "true" : "false";
                    break;
                case 'integer':
                    $value = filter_var($value, FILTER_VALIDATE_INT);
                    if ($value === false) {
                        throw new Exception\Request("Invalid value: '" . $param . "' must be an integer");
                    }
                    break;
                default:
                    // No-op, take the value whatever it is
                    break;
            }

            $query[$param] = $value;
        }

        return $query;
    }

    /**
     * @param string|Number $number Number onject to populate, deprecated
     */
    private function handleNumberSearchResult(IterableAPICollection $response, $number = null) : array
    {
        // We're going to return a list of numbers
        $numbers = [];

        // Legacy - If the user passed in a number object, populate that object
        // @deprecated This will eventually return a new clean object
        if (count($response) === 1 && $number instanceof Number) {
            $number->fromArray($response->current()->toArray());
            $numbers[] = $number;
        } else {
            foreach ($response as $rawNumber) {
                $numbers[] = $rawNumber;
            }
        }

        return $numbers;
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
