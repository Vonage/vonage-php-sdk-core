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
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\ThrottleException;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\IterableAPICollection;
use Vonage\Numbers\Filter\AvailableNumbers;
use Vonage\Numbers\Filter\OwnedNumbers;

use function array_key_exists;
use function count;
use function filter_var;
use function get_class;
use function is_null;
use function sleep;
use function trigger_error;

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

    /**
     * Shim to handle older instantiations of this class
     * Will change in v3 to just return the required API object
     */
    public function getApiResource(): APIResource
    {
        return $this->api;
    }

    /**
     * @param mixed $number Number to update
     * @param string|null $id MSISDN to look
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     *
     * @todo Clean up the logic here, we are doing a lot of GET requests
     */
    public function update(Number $number, ?string $id = null): Number
    {
        if (!is_null($id)) {
            $update = $this->get($id);
        }

        $body = $number->toArray();

        if (!isset($update) && !isset($body['country'])) {
            $data = $this->get($number->getId());
            $body['msisdn'] = $data->getId();
            $body['country'] = $data->getCountry();
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

        // some of the groundwork for depreciation removal has happened, but this is
        // way too volatile to decipher right now
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
     * @param string $number Number to fetch
     *
     * @return Number
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function get(string $number): Number
    {
        $items = $this->searchOwned($number);

        // This is legacy behaviour, so we need to keep it even though
        // it isn't technically the correct message
        if (count($items) !== 1) {
            throw new ClientException\Request('number not found', 404);
        }

        return $items[0];
    }

    /**
     * Returns a set of numbers for the specified country
     *
     * @param string $country The two character country code in ISO 3166-1 alpha-2 format
     * @param FilterInterface $options Additional options, see
     * https://developer.nexmo.com/api/numbers#getAvailableNumbers
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function searchAvailable(string $country, FilterInterface $options): array
    {
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

        $options = $options->getQuery();

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
     * @param FilterInterface|null $number
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function searchOwned(FilterInterface $number = null): array
    {
        if ($number !== null) {
            $options = $number->getQuery();
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
     * @throws ClientException\Request
     */
    protected function parseParameters(array $possibleParameters, array $data = []): array
    {
        $query = [];

        foreach ($data as $param => $value) {
            if (!array_key_exists($param, $possibleParameters)) {
                throw new ClientException\Request("Unknown option: '" . $param . "'");
            }

            switch ($possibleParameters[$param]) {
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    if (is_null($value)) {
                        throw new ClientException\Request("Invalid value: '" . $param . "' must be a boolean value");
                    }
                    $value = $value ? "true" : "false";
                    break;
                case 'integer':
                    $value = filter_var($value, FILTER_VALIDATE_INT);
                    if ($value === false) {
                        throw new ClientException\Request("Invalid value: '" . $param . "' must be an integer");
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
     * @param $number deprecated
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
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
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function purchase($number, ?string $country = null): void
    {
        // We cheat here and fetch a number using the API so that we have the country code which is required
        // to make a purchase request
        if (!$number instanceof Number) {
            if (!$country) {
                throw new ClientException\Exception(
                    "You must supply a country in addition to a number to purchase a number"
                );
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
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
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
