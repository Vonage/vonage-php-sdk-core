<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Numbers;

use Vonage\Client\APIClient;
use Vonage\Client\Exception;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\Exception\ThrottleException;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\Filter\KeyValueFilter;
use Vonage\Entity\IterableAPICollection;
use Vonage\Numbers\Filter\AvailableNumbers;
use Vonage\Numbers\Filter\OwnedNumbers;

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
     * Shim to handle older instatiations of this class
     * Will change in v3 to just return the required API object
     */
    public function getApiResource() : APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUrl($this->getClient()->getRestUrl())
                ->setIsHAL(false)
            ;
            $this->api = $api;
        }
        return $this->api;
    }

    /**
     * @todo Clean up the logic here, we are doing a lot of GET requests
     *
     * @param string|Number $number Number to update
     * @param string $id MSISDN to look
     */
    public function update($number, ?string $id = null) : Number
    {
        if (!$number instanceof Number) {
            trigger_error(
                "Passing a string to `Vonage\Number\Client::update()` is deprecated, please pass a `Number` object instead",
                E_USER_DEPRECATED
            );
        }

        if (!is_null($id)) {
            $update = $this->get($id);
        }

        if ($number instanceof Number) {
            $body = $number->toArray();
            if (!isset($update) and !isset($body['country'])) {
                $data = $this->get($number->getId());
                $body['msisdn'] = $data->getId();
                $body['country'] = $data->getCountry();
            }
        } else {
            $body = $number;
        }

        if (isset($update)) {
            $body['msisdn'] = $update->getId();
            $body['country'] = $update->getCountry();
        }

        unset($body['features'], $body['type']);

        $api = $this->getApiResource();
        $api->submit($body, '/number/update');

        // Yes, the following blocks of code are ugly. This will get refactored
        // in v3 where we no longer have to worry about multiple types of
        // inputs for $number
        if (isset($update) and ($number instanceof Number)) {
            try {
                return $this->get($number);
            } catch (ThrottleException $e) {
                sleep(1); // This API is 1 request per second :/
                return $this->get($number);
            }
        }

        if ($number instanceof Number) {
            try {
                return @$this->get($number);
            } catch (ThrottleException $e) {
                sleep(1); // This API is 1 request per second :/
                return @$this->get($number);
            }
        }

        try {
            return $this->get($body['msisdn']);
        } catch (ThrottleException $e) {
            sleep(1); // This API is 1 request per second :/
            return $this->get($body['msisdn']);
        }
    }

    /**
     * Returns a number
     *
     * @param Number|string $number Number to fetch, deprecating passing a `Number` object
     */
    public function get($number = null) : Number
    {
        if (is_null($number)) {
            trigger_error(
                'Calling Vonage\Numbers\Client::get() without a parameter is deprecated, please use `searchOwned()` or `searchAvailable()` instead',
                E_USER_DEPRECATED
            );
        }

        if ($number instanceof Number) {
            trigger_error(
                'Calling Vonage\Numbers\Client::get() with a `Number` object is deprecated, please pass a string MSISDN instead',
                E_USER_DEPRECATED
            );
        }

        $items =  $this->searchOwned($number);

        // This is legacy behaviour, so we need to keep it even though
        // it isn't technically the correct message
        if (count($items) != 1) {
            throw new Exception\Request('number not found', 404);
        }

        return $items[0];
    }

    /**
     * @param null|string|Number $number
     * @return array []Number
     * @deprecated Use `searchOwned` instead
     */
    public function search($number = null)
    {
        return $this->searchOwned($number);
    }

    /**
     * Returns a set of numbers for the specified country
     *
     * @param string $country The two character country code in ISO 3166-1 alpha-2 format
     * @param array $options Additional options, see https://developer.nexmo.com/api/numbers#getAvailableNumbers
     */
    public function searchAvailable($country, $options = []) : array
    {
        if (is_array($options)) {
            if (!empty($options)) {
                trigger_error(
                    'Passing an array to ' . get_class($this) . '::searchAvailable() is deprecated, pass a FilterInterface instead',
                    E_USER_DEPRECATED
                );
            }
        }

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

        $options = $this->parseParameters($possibleParameters, $options);
        $options = new AvailableNumbers($options);
        $api = $this->getApiResource();
        $api->setCollectionName('numbers');

        $response = $api->search(
            new AvailableNumbers($options->getQuery() + ['country' => $country]),
            '/number/search'
        );
        $response->setHydrator(new Hydrator());
        $response->setAutoAdvance(false); // The search results on this can be quite large

        return $this->handleNumberSearchResult($response, null);
    }

    /**
     * Returns a set of numbers for the specified country
     *
     * @param string|Number $number Number to search for, if any
     * @param array $options Additional options, see https://developer.nexmo.com/api/numbers#getOwnedNumbers
     */
    public function searchOwned($number = null, array $options = []) : array
    {
        if (!empty($options)) {
            trigger_error(
                'Passing a array for Parameter 2 into ' . get_class($this) . '::searchOwned() is deprecated, please pass a FilterInterface as the first parameter only',
                E_USER_DEPRECATED
            );
        }

        if ($number !== null) {
            if ($number instanceof FilterInterface) {
                $options = $number->getQuery() + $options;
            } elseif ($number instanceof Number) {
                trigger_error(
                    'Passing a Number object into ' . get_class($this) . '::searchOwned() is deprecated, please pass a FilterInterface',
                    E_USER_DEPRECATED
                );
                $options['pattern'] = (string) $number->getId();
            } else {
                $options['pattern'] = (string) $number;
            }
        }

        // These are all optional parameters
        $possibleParameters = [
            'country' => 'string',
            'pattern' => 'string',
            'search_pattern' => 'integer',
            'size' => 'integer',
            'index' => 'integer',
            'has_application' => 'boolean',
            'application_id' => 'string'
        ];

        $options = $this->parseParameters($possibleParameters, $options);
        $options = new OwnedNumbers($options);
        $api = $this->getApiResource();
        $api->setCollectionName('numbers');

        $response = $api->search($options, '/account/numbers');
        $response->setHydrator(new Hydrator());
        $response->setAutoAdvance(false); // The search results on this can be quite large

        return $this->handleNumberSearchResult($response, $number);
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

    /**
     * @param Number|string $number Number to purchase
     */
    public function purchase($number, ?string $country = null) : void
    {
        // We cheat here and fetch a number using the API so that we have the country code which is required
        // to make a purchase request
        if (!$number instanceof Number) {
            if (!$country) {
                throw new Exception\Exception("You must supply a country in addition to a number to purchase a number");
            }

            trigger_error(
                'Passing a Number object to Vonage\Number\Client::purchase() is being deprecated, please pass a string MSISDN instead',
                E_USER_DEPRECATED
            );
            $number = new Number($number, $country);
        }

        $body = [
            'msisdn' => $number->getMsisdn(),
            'country' => $number->getCountry()
        ];

        $api = $this->getApiResource();
        $api->setBaseUri('/number/buy');
        $api->submit($body);
    }

    /**
     * @param Number|string $number Number to cancel
     */
    public function cancel($number, ?string $country = null) : void
    {
        // We cheat here and fetch a number using the API so that we have the country code which is required
        // to make a cancel request
        if (!$number instanceof Number) {
            $number = $this->get($number);
        } else {
            trigger_error(
                'Passing a Number object to Vonage\Number\Client::cancel() is being deprecated, please pass a string MSISDN instead',
                E_USER_DEPRECATED
            );

            if (!is_null($country)) {
                $number = new Number($number, $country);
            }
        }

        $body = [
            'msisdn' => $number->getMsisdn(),
            'country' => $number->getCountry()
        ];

        $api = $this->getApiResource();
        $api->setBaseUri('/number/cancel');
        $api->submit($body);
    }
}
