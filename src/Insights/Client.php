<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Insights;

use Nexmo\Client\APIClient;
use Nexmo\Numbers\Number;
use Nexmo\Client\Exception;
use Nexmo\Client\APIResource;
use Nexmo\Entity\Filter\KeyValueFilter;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\ClientAwareInterface;

/**
 * Class Client
 */
class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $api;

    public function __construct(APIResource $api)
    {
        $this->api = $api;
    }

    public function getApiResource() : APIResource
    {
        return $this->api;
    }

    public function basic($number) : Basic
    {
        $insightsResults = $this->makeRequest('/ni/basic/json', $number);

        $basic = new Basic($insightsResults['national_format_number']);
        $basic->fromArray($insightsResults);
        return $basic;
    }

    public function standardCNam($number) : StandardCnam
    {
        $insightsResults = $this->makeRequest('/ni/standard/json', $number, ['cnam' => 'true']);
        $standard = new StandardCnam($insightsResults['national_format_number']);
        $standard->fromArray($insightsResults);
        return $standard;
    }

    public function advancedCnam($number) : AdvancedCnam
    {
        $insightsResults = $this->makeRequest('/ni/advanced/json', $number, ['cnam' => 'true']);
        $standard = new AdvancedCnam($insightsResults['national_format_number']);
        $standard->fromArray($insightsResults);
        return $standard;
    }

    public function standard($number, bool $useCnam = false) : Standard
    {
        $insightsResults = $this->makeRequest('/ni/standard/json', $number);
        $standard = new Standard($insightsResults['national_format_number']);
        $standard->fromArray($insightsResults);
        return $standard;
    }

    public function advanced($number) : Advanced
    {
        $insightsResults = $this->makeRequest('/ni/advanced/json', $number);
        $advanced = new Advanced($insightsResults['national_format_number']);
        $advanced->fromArray($insightsResults);
        return $advanced;
    }

    public function advancedAsync($number, string $webhook) : void
    {
        // This method does not have a return value as it's async. If there is no exception thrown
        // We can assume that everything is fine
        $this->makeRequest('/ni/advanced/async/json', $number, ['callback' => $webhook]);
    }

    /**
     * Common code for generating a request
     */
    public function makeRequest(string $path, $number, array $additionalParams = []) : array
    {
        $api = $this->getApiResource();
        $api->setBaseUri($path);

        if ($number instanceof Number) {
            $number = $number->getMsisdn();
        }

        $query = ['number' => $number] + $additionalParams;
        $result = $api->search(new KeyValueFilter($query));
        $data = $result->getPageData();

        // check the status field in response (HTTP status is 200 even for errors)
        if ($data['status'] != 0) {
            throw $this->getNIException($data);
        }

        return $data;
    }

    /**
     * Parses response body for an error and throws it
     * This API returns a 200 on an error, so does not get caught by the normal
     * error checking. We check for a status and message manually.
     */
    protected function getNIException(array $body) : Exception\Request
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

        $e = new Exception\Request($message, $status);
        return $e;
    }
}
