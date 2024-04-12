<?php

declare(strict_types=1);

namespace Vonage\Insights;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Exception;
use Vonage\Client\Exception\Request;
use Vonage\Client\Exception\Server;
use Vonage\Entity\Filter\KeyValueFilter;
use Vonage\Entity\IterableAPICollection;
use Vonage\Numbers\Number;

/**
 * Class Client
 */
class Client implements APIClient
{
    protected array $chargeableCodes = [0, 43, 44, 45];

    public function __construct(protected ?APIResource $api = null)
    {
    }

    public function getApiResource(): APIResource
    {
        return clone $this->api;
    }

    /**
     * @param $number
     *
     * @return Basic
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws Request
     * @throws Server
     */
    public function basic($number): Basic
    {
        $insightsResults = $this->makeRequest('/ni/basic/json', $number);

        $basic = new Basic($insightsResults['national_format_number']);
        $basic->fromArray($insightsResults);
        return $basic;
    }

    /**
     * @param $number
     *
     * @return StandardCnam
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws Request
     * @throws Server
     */
    public function standardCNam($number): StandardCnam
    {
        $insightsResults = $this->makeRequest('/ni/standard/json', $number, ['cnam' => 'true']);
        $standard = new StandardCnam($insightsResults['national_format_number']);
        $standard->fromArray($insightsResults);
        return $standard;
    }

    /**
     * @param $number
     *
     * @return AdvancedCnam
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws Request
     * @throws Server
     */
    public function advancedCnam($number): AdvancedCnam
    {
        $insightsResults = $this->makeRequest('/ni/advanced/json', $number, ['cnam' => 'true']);
        $standard = new AdvancedCnam($insightsResults['national_format_number']);
        $standard->fromArray($insightsResults);
        return $standard;
    }

    /**
     * @param $number
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function standard($number): Standard
    {
        $insightsResults = $this->makeRequest('/ni/standard/json', $number);
        $standard = new Standard($insightsResults['national_format_number']);
        $standard->fromArray($insightsResults);
        return $standard;
    }

    /**
     * @param $number
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function advanced($number): Advanced
    {
        $insightsResults = $this->makeRequest('/ni/advanced/json', $number);
        $advanced = new Advanced($insightsResults['national_format_number']);
        $advanced->fromArray($insightsResults);
        return $advanced;
    }

    /**
     * @param $number
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     */
    public function advancedAsync($number, string $webhook): void
    {
        // This method does not have a return value as it's async. If there is no exception thrown
        // We can assume that everything is fine
        $this->makeRequest('/ni/advanced/async/json', $number, ['callback' => $webhook]);
    }

    /**
     * Common code for generating a request
     *
     * @param $number
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function makeRequest(string $path, $number, array $additionalParams = []): array
    {
        $api = $this->getApiResource();
        $api->setBaseUri($path);
        $collectionPrototype = new IterableAPICollection();
        $collectionPrototype->setHasPagination(false);
        $api->setCollectionPrototype($collectionPrototype);

        if ($number instanceof Number) {
            $number = $number->getMsisdn();
        }

        $query = ['number' => $number] + $additionalParams;
        $result = $api->search(new KeyValueFilter($query));
        $data = $result->getPageData();

        // check the status field in response (HTTP status is 200 even for errors)
        if (! in_array((int)$data['status'], $this->chargeableCodes, true)) {
            throw $this->getNIException($data);
        }

        return $data;
    }

    /**
     * Parses response body for an error and throws it
     * This API returns a 200 on an error, so does not get caught by the normal
     * error checking. We check for a status and message manually.
     */
    protected function getNIException(array $body): ClientException\Request
    {
        $status = $body['status'];
        $message = "Error: ";

        if (isset($body['status_message'])) {
            $message .= $body['status_message'];
        }

        // the advanced async endpoint returns status detail in another field
        // this is a workaround
        if (isset($body['error_text'])) {
            $message .= $body['error_text'];
        }

        return new ClientException\Request($message, $status);
    }
}
