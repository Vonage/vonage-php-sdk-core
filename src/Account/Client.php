<?php

namespace Nexmo\Account;

use Nexmo\Client\ClientAwareInterface;
use Nexmo\Client\ClientAwareTrait;
use Nexmo\Client\Exception;
use Nexmo\Client\OpenAPIResource;
use Nexmo\Entity\SimpleFilter;

/**
 * @todo Unify the exception handling to avoid duplicated code and logic (ie: getPrefixPricing())
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

    public function getPrefixPricing($prefix) : array
    {
        $api = clone $this->api;
        $api->setBaseUri('/account/get-prefix-pricing/outbound');
        $api->setCollectionName('prices');

        $data = $api->search(new SimpleFilter(['prefix' => $prefix]));
        
        if (count($data) == 0) {
            return [];
        }

        // Multiple countries can match each prefix
        $prices = [];

        foreach ($data as $p) {
            $prefixPrice = new PrefixPrice();
            $prefixPrice->jsonUnserialize($p);
            $prices[] = $prefixPrice;
        }

        return $prices;
    }

    public function getSmsPrice($country)
    {
        $body = $this->makePricingRequest($country, 'sms');
        $smsPrice = new SmsPrice();
        $smsPrice->jsonUnserialize($body);
        return $smsPrice;
    }

    public function getVoicePrice($country)
    {
        $body = $this->makePricingRequest($country, 'voice');
        $voicePrice = new VoicePrice();
        $voicePrice->jsonUnserialize($body);
        return $voicePrice;
    }

    /**
     * @todo This should return an empty result instead of throwing an Exception on no results
     */
    protected function makePricingRequest($country, $pricingType) : array
    {
        $api = clone $this->api;
        $api->setBaseUri('/account/get-pricing/outbound/' . $pricingType);
        $results = $api->search(new SimpleFilter(['country' => $country]));
        $data = $results->getPageData();

        if (is_null($data)) {
            throw new Exception\Server('No results found');
        }

        return $data;
    }

    /**
     * @todo This needs further investigated to see if '' can even be returned from this endpoint
     */
    public function getBalance() : Balance
    {
        $data = $this->api->get('get-balance');

        if (is_null($data)) {
            throw new Exception\Server('No results found');
        }

        $balance = new Balance($data['value'], $data['autoReload']);
        return $balance;
    }

    public function topUp($trx) : void
    {
        $api = clone $this->api;
        $api->setBaseUri('/account/top-up');
        $api->submit(['trx' => $trx]);
    }

    public function getConfig() : Config
    {
        $api = clone $this->api;
        $api->setBaseUri('/account/settings');
        $body = $api->submit();

        if ($body === '') {
            throw new Exception\Server('Response was empty');
        }

        $body = json_decode($body, true);
        $config = new Config(
            $body['mo-callback-url'],
            $body['dr-callback-url'],
            $body['max-outbound-request'],
            $body['max-inbound-request'],
            $body['max-calls-per-second']
        );
        return $config;
    }

    public function updateConfig(array $options) : Config
    {
        // supported options are SMS Callback and DR Callback
        $params = [];
        if (isset($options['sms_callback_url'])) {
            $params['moCallBackUrl'] = $options['sms_callback_url'];
        }

        if (isset($options['dr_callback_url'])) {
            $params['drCallBackUrl'] = $options['dr_callback_url'];
        }

        $api = clone $this->api;
        $api->setBaseUri('/account/settings');

        $rawBody = $api->submit($params);

        if ($rawBody === '') {
            throw new Exception\Server('Response was empty');
        }

        $body = json_decode($rawBody, true);

        $config = new Config(
            $body['mo-callback-url'],
            $body['dr-callback-url'],
            $body['max-outbound-request'],
            $body['max-inbound-request'],
            $body['max-calls-per-second']
        );
        return $config;
    }

    public function listSecrets(string $apiKey) : SecretCollection
    {
        $api = clone $this->api;
        $api->setBaseUrl($this->getClient()->getApiUrl());
        $api->setBaseUri('/accounts');
        
        $data = $api->get($apiKey . '/secrets');

        return SecretCollection::fromApi($data);
    }

    public function getSecret(string $apiKey, string $secretId) : Secret
    {
        $api = clone $this->api;
        $api->setBaseUrl($this->getClient()->getApiUrl());
        $api->setBaseUri('/accounts');
        
        $data = $api->get($apiKey . '/secrets/' . $secretId);

        return Secret::fromApi($data);
    }

    public function createSecret(string $apiKey, string $newSecret) : Secret
    {
        $api = clone $this->api;
        $api->setBaseUrl($this->getClient()->getApiUrl());
        $api->setBaseUri('/accounts/' . $apiKey . '/secrets');
        
        $response = $api->create(['secret' => $newSecret]);
        
        return Secret::fromApi($response);
    }

    public function deleteSecret(string $apiKey, string $secretId) : void
    {
        $api = clone $this->api;
        $api->setBaseUrl($this->getClient()->getApiUrl());
        $api->setBaseUri('/accounts/' . $apiKey . '/secrets');
        $api->delete($secretId);
    }
}
