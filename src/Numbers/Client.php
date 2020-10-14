<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Numbers;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception;
use Vonage\Client\Exception\ThrottleException;
use Vonage\Entity\Filter\FilterInterface;
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

    /**
     * Client constructor.
     *
     * @param APIResource|null $api
     */
    public function __construct(APIResource $api = null)
    {
        $this->api = $api;
    }

    /**
     * Shim to handle older instantiations of this class
     * Will change in v3 to just return the required API object
     *
     * @return APIResource
     */
    public function getApiResource(): APIResource
    {
        if (is_null($this->api)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUrl($this->getClient()->getRestUrl())
                ->setIsHAL(false);
            $this->api = $api;
        }

        return $this->api;
    }

    /**
     * @param mixed $number Number to update
     * @param string|null $id MSISDN to look
     * @return Number
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @todo Clean up the logic here, we are doing a lot of GET requests
     */
    public function update($number, ?string $id = null): Number
    {
        if (!$number instanceof Number) {
            trigger_error(
                'Passing a string to `Vonage\Number\Client::update()` is deprecated, ' .
                'please pass a `Number` object instead',
                E_USER_DEPRECATED
            );
        }

        if (!is_null($id)) {
            $update = $this->get($id);
        }

        if ($number instanceof Number) {
            $body = $number->toArray();
            if (!isset($update) && !isset($body['country'])) {
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
        if (isset($update) && ($number instanceof Number)) {
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
     * @param null $number Number to fetch, deprecating passing a `Number` object
     * @return Number
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function get($number = null): Number
    {
        if (is_null($number)) {
            trigger_error(
                'Calling Vonage\Numbers\Client::get() without a parameter is deprecated, ' .
                'please use `searchOwned()` or `searchAvailable()` instead',
                E_USER_DEPRECATED
            );
        }

        if ($number instanceof Number) {
            trigger_error(
                'Calling Vonage\Numbers\Client::get() with a `Number` object is deprecated, ' .
                'please pass a string MSISDN instead',
                E_USER_DEPRECATED
            );
        }

        $items = $this->searchOwned($number);

        // This is legacy behaviour, so we need to keep it even though
        // it isn't technically the correct message
        if (count($items) !== 1) {
            throw new Exception\Request('number not found', 404);
        }

        return $items[0];
    }

    /**
     * @param null|string|Number $number
     * @return array []Number
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     * @deprecated Use `searchOwned` instead
     */
    public function search($number = null): array
    {
        return $this->searchOwned($number);
    }

    /**
     * Returns a set of numbers for the specified country
     *
     * @param string $country The two character country code in ISO 3166-1 alpha-2 format
     * @param array $options Additional options, see https://developer.nexmo.com/api/numbers#getAvailableNumbers
     * @return array
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function searchAvailable(string $country, $options = []): array
    {
        if (is_array($options) && !empty($options)) {
            trigger_error(
                'Passing an array to ' . get_class($this) . '::searchAvailable() is deprecated, ' .
                'pass a FilterInterface instead',
                E_USER_DEPRECATED
            );
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

        if ($options instanceof FilterInterface) {
            $options = $options->getQuery();
        }

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
     * @param null $number
     * @param array $options
     * @return array
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function searchOwned($number = null, array $options = []): array
    {
        if (!empty($options)) {
            trigger_error(
                'Passing a array for Parameter 2 into ' . get_class($this) . '::searchOwned() ' .
                'is deprecated, please pass a FilterInterface as the first parameter only',
                E_USER_DEPRECATED
            );
        }

        if ($number !== null) {
            if ($number instanceof FilterInterface) {
                $options = $number->getQuery() + $options;
            } elseif ($number instanceof Number) {
                trigger_error(
                    'Passing a Number object into ' . get_class($this) . '::searchOwned() is deprecated, ' .
                    'please pass a FilterInterface',
                    E_USER_DEPRECATED
                );
                $options['pattern'] = (string)$number->getId();
            } else {
                $options['pattern'] = (string)$number;
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
     *
     * @param array $possibleParameters
     * @param array $data
     * @return array
     * @throws Exception\Request
     */
    protected function parseParameters(array $possibleParameters, array $data = []): array
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
     * @param IterableAPICollection $response
     * @param null $number deprecated
     * @return array
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     * @throws ClientExceptionInterface
     */
    private function handleNumberSearchResult(IterableAPICollection $response, $number = null): array
    {
        // We're going to return a list of numbers
        $numbers = [];

        // Legacy - If the user passed in a number object, populate that object
        // @deprecated This will eventually return a new clean object
        if ($number instanceof Number && count($response) === 1) {
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
     * @param $number
     * @param string|null $country
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     */
    public function purchase($number, ?string $country = null): void
    {
        // We cheat here and fetch a number using the API so that we have the country code which is required
        // to make a purchase request
        if (!$number instanceof Number) {
            if (!$country) {
                throw new Exception\Exception("You must supply a country in addition to a number to purchase a number");
            }

            trigger_error(
                'Passing a Number object to Vonage\Number\Client::purchase() is being deprecated, ' .
                'please pass a string MSISDN instead',
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
     * @param $number
     * @param string|null $country
     * @throws ClientExceptionInterface
     * @throws Exception\Exception
     * @throws Exception\Request
     * @throws Exception\Server
     */
    public function cancel($number, ?string $country = null): void
    {
        // We cheat here and fetch a number using the API so that we have the country code which is required
        // to make a cancel request
        if (!$number instanceof Number) {
            $number = $this->get($number);
        } else {
            trigger_error(
                'Passing a Number object to Vonage\Number\Client::cancel() is being deprecated, ' .
                'please pass a string MSISDN instead',
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
