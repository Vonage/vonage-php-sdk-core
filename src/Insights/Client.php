<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Insights;

use Nexmo\Client\Exception;
use Nexmo\Entity\SimpleFilter;
use Nexmo\Client\OpenAPIResource;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\ClientAwareInterface;

/**
 * Class Client
 */
class Client implements ClientAwareInterface
{
    use ClientAwareTrait;

    /**
     * @var OpenAPIResource
     */
    protected $api;

    public function __construct(OpenAPIResource $api)
    {
        $this->api = $api;
    }

    public function basic(string $number) : Basic
    {
        $insightsResults = $this->makeRequest('/ni/basic/json', $number);

        $basic = new Basic($insightsResults['national_format_number']);
        $basic->jsonUnserialize($insightsResults);
        return $basic;
    }

    public function standardCNam(string $number) : StandardCnam
    {
        $insightsResults = $this->makeRequest('/ni/standard/json', $number, ['cnam' => 'true']);
        $standard = new StandardCnam($insightsResults['national_format_number']);
        $standard->jsonUnserialize($insightsResults);
        return $standard;
    }

    public function advancedCnam(string $number) : AdvancedCnam
    {
        $insightsResults = $this->makeRequest('/ni/advanced/json', $number, ['cnam' => 'true']);
        $standard = new AdvancedCnam($insightsResults['national_format_number']);
        $standard->jsonUnserialize($insightsResults);
        return $standard;
    }

    public function standard(string $number, bool $useCnam = false) : Standard
    {
        $insightsResults = $this->makeRequest('/ni/standard/json', $number);
        $standard = new Standard($insightsResults['national_format_number']);
        $standard->jsonUnserialize($insightsResults);
        return $standard;
    }

    public function advanced(string $number) : Advanced
    {
        $insightsResults = $this->makeRequest('/ni/advanced/json', $number);
        $advanced = new Advanced($insightsResults['national_format_number']);
        $advanced->jsonUnserialize($insightsResults);
        return $advanced;
    }

    /**
     * Performs an advanced number insight lookup by async
     * 
     * @param string $webhook URL to use as a callback
     */
    public function advancedAsync(string $number, string $webhook)
    {
        // This method does not have a return value as it's async. If there is no exception thrown
        // We can assume that everything is fine
        $this->makeRequest('/ni/advanced/async/json', $number, ['callback' => $webhook]);
    }

    public function makeRequest(string $path, string $number, array $additionalParams = []) : array
    {
        $api = clone $this->api;
        $api->setBaseUri($path);

        $params = ['number' => $number] + $additionalParams;
        $response = $api->search(new SimpleFilter($params));
        $insightsResults = $response->current();

        if ($insightsResults['status'] != 0) {
            throw $this->getNIException($insightsResults);
        }

        return $insightsResults;
    }

    /**
     * Number Insights special error handling
     * Number Insights returns a 200 even on most errors, so this does some
     * additional checking to the JSON response to see if we actually errored.
     */
    protected function getNIException($body)
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
