<?php

declare(strict_types=1);

namespace Vonage\Numbers;

use Psr\Http\Client\ClientExceptionInterface;
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

class Client
{
    public function __construct(protected APIResource $api)
    {
    }

    /**
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

        $this->api->submit($body, '/number/update');

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
     * @param string $number Number to fetch, deprecating passing a `Number` object
     *
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
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function searchAvailable(string $country, ?FilterInterface $options = null): array
    {
        if (is_null($options)) {
            $options = new AvailableNumbers([
                'country' => $country
            ]);
        }

        $this->api->setCollectionName('numbers');

        $response = $this->api->search(
            new AvailableNumbers($options->getQuery() + ['country' => $country]),
            '/number/search'
        );

        $response->setHydrator(new Hydrator());
        $response->setAutoAdvance(false); // The search results on this can be quite large

        return $this->handleNumberSearchResult($response, null);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function searchOwned($number = null, ?FilterInterface $options = null): array
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

        $this->api->setCollectionName('numbers');

        $response = $this->api->search($options, '/account/numbers');
        $response->setHydrator(new Hydrator());
        $response->setAutoAdvance(false); // The search results on this can be quite large

        return $this->handleNumberSearchResult($response, $number);
    }

    /**
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    private function handleNumberSearchResult(IterableAPICollection $response): array
    {
        // We're going to return a list of numbers
        $numbers = [];

        foreach ($response as $rawNumber) {
            $numbers[] = $rawNumber;
        }

        return $numbers;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function purchase(string $number, string $country): void
    {
        $body = [
            'msisdn' => $number,
            'country' => $country
        ];

        $this->api->setBaseUri('/number/buy');
        $this->api->submit($body);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function cancel(string $number): void
    {
        $number = $this->get($number);

        $body = [
            'msisdn' => $number->getMsisdn(),
            'country' => $number->getCountry()
        ];

        $this->api->setBaseUri('/number/cancel');
        $this->api->submit($body);
    }
}
