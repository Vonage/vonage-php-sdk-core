<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Numbers;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Exception;
use Vonage\Client\Exception\Request;
use Vonage\Client\Exception\Server;
use Vonage\Client\Exception\ThrottleException;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\IterableAPICollection;
use Vonage\Numbers\Filter\AvailableNumbers;
use Vonage\Numbers\Filter\OwnedNumbers;

use function count;
use function is_null;
use function sleep;
use function trigger_error;

class Client implements APIClient
{
    public function __construct(protected ?APIResource $api = null)
    {
    }

    public function getApiResource(): APIResource
    {
        return $this->api;
    }

    /**
     * @param Number $number
     * @param string|null $id
     *
     * @return Number
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws Request
     * @throws Server
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

        if (isset($update)) {
            try {
                return $this->get($number->getId());
            } catch (ThrottleException) {
                sleep(1); // This API is 1 request per second :/
                return $this->get($number->getId());
            }
        }

        try {
            return $this->get($number->getId());
        } catch (ThrottleException) {
            sleep(1); // This API is 1 request per second :/
            return $this->get($number->getId());
        }
    }

    /**
     * Returns a number
     *
     * @param string $number Number to fetch, deprecating passing a `Number` object
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
     * @param FilterInterface $options Additional options, see https://developer.nexmo.com/api/numbers#getAvailableNumbers
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function searchAvailable(string $country, FilterInterface $options = null): array
    {
        if (is_null($options)) {
            $options = new AvailableNumbers([
                'country' => $country
            ]);
        }

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
     * @param FilterInterface|null $options
     *
     * @return array
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function searchOwned($number = null, FilterInterface $options = null): array
    {
        if ($number !== null) {
            if ($options !== null) {
                $options->setPattern($number);
            } else {
                $options = new OwnedNumbers([
                    'pattern' => $number
                ]);
            }
        }

        $api = $this->getApiResource();
        $api->setCollectionName('numbers');

        $response = $api->search($options, '/account/numbers');
        $response->setHydrator(new Hydrator());
        $response->setAutoAdvance(false); // The search results on this can be quite large

        return $this->handleNumberSearchResult($response, $number);
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
     * @param string $number
     * @param string|null $country
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function cancel(string $number, ?string $country = null): void
    {
        $number = $this->get($number);

        $body = [
            'msisdn' => $number->getMsisdn(),
            'country' => $number->getCountry()
        ];

        $api = $this->getApiResource();
        $api->setBaseUri('/number/cancel');
        $api->submit($body);
    }
}
