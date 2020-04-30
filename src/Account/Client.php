<?php

namespace Nexmo\Account;

use Nexmo\Client\APIClient;
use Nexmo\Account\Exception\NotFoundException;
use Nexmo\Client\Exception;
use Nexmo\Client\APIResource;
use Nexmo\Client\Exception\Request as ExceptionRequest;
use Nexmo\Client\Exception\Validation;
use Nexmo\Entity\Filter\KeyValueFilter;

/**
 * @todo Unify the exception handling to avoid duplicated code and logic (ie: getPrefixPricing())
 */
class Client implements APIClient
{
    /**
     * @var APIResource
     */
    protected $accountAPI;

    /**
     * @var APIResource
     */
    protected $secretsAPI;

    /**
     * @var PriceFactory
     */
    protected $priceFactory;

    public function __construct(APIResource $accountAPI, APIResource $secretsAPI, PriceFactory $priceFactory)
    {
        $this->accountAPI = $accountAPI;
        $this->secretsAPI = $secretsAPI;
        $this->priceFactory = $priceFactory;
    }

    public function getAPIResource(): APIResource
    {
        return $this->accountAPI;
    }

    /**
     * Returns pricing based on the prefix requested
     *
     * @return array<int, PrefixPrice>
     */
    public function getPrefixPricing(string $prefix) : array
    {
        $data = $this->accountAPI->get(
            'get-prefix-pricing/outbound',
            (new KeyValueFilter(['prefix' => $prefix]))->getQuery()
        );
        
        $prices = [];
        foreach ($data['prices'] as $priceData) {
            /** @var PrefixPrice $price */
            $price = $this->priceFactory->build($priceData, PriceFactory::TYPE_PREFIX);
            $prices[] = $price;
        }

        if (empty($prices)) {
            throw new NotFoundException('No results found');
        }
       
        return $prices;
    }

    /**
     * Get SMS Pricing based on Country
     */
    public function getSmsPrice(string $country) : SmsPrice
    {
        $body = $this->makePricingRequest($country, 'sms');
        
        /** @var SmsPrice $price */
        $price = $this->priceFactory->build($body, PriceFactory::TYPE_SMS);
        return $price;
    }

    /**
     * Get Voice pricing based on Country
     */
    public function getVoicePrice(string $country) : VoicePrice
    {
        $body = $this->makePricingRequest($country, 'voice');

        /** @var VoicePrice $price */
        $price = $this->priceFactory->build($body, PriceFactory::TYPE_VOICE);
        return $price;
    }

    /**
     * @return array<string, array|string>
     */
    protected function makePricingRequest(string $country, string $pricingType) : array
    {
        $data = $this->accountAPI->get(
            'get-pricing/outbound/' . $pricingType,
            (new KeyValueFilter(['country' => $country]))->getQuery()
        );

        if (is_null($data)) {
            throw new NotFoundException('No results found');
        }

        return $data;
    }

    /**
     * Gets the accounts current balance in Euros
     *
     * @todo This needs further investigated to see if '' can even be returned from this endpoint
     */
    public function getBalance() : Balance
    {
        $data = $this->accountAPI->get('get-balance');
        
        if (is_null($data)) {
            throw new Exception\Server('Unable to retrieve balance');
        }

        $balance = new Balance($data['value'], $data['autoReload']);
        return $balance;
    }

    public function topUp(string $trx) : void
    {
        $this->accountAPI->submit(['trx' => $trx], '/top-up');
    }

    /**
     * Return the account settings
     */
    public function getConfig() : Config
    {
        $body = $this->accountAPI->submit([], '/settings');

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

    /**
     * Update account config
     * @param array<string, string> $options Callback options to set for the account
     */
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

        $rawBody = $this->accountAPI->submit($params, '/settings');

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
        $data = $this->secretsAPI->get($apiKey . '/secrets');
        $secrets = [];
        foreach ($data['_embedded']['secrets'] as $secretData) {
            $secrets[] = new Secret($secretData['id'], $secretData['created_at'], $secretData['_links']);
        }

        return new SecretCollection($secrets, $data['_links']);
    }

    public function getSecret(string $apiKey, string $secretId) : Secret
    {
        $data = $this->secretsAPI->get($apiKey . '/secrets/' . $secretId);
        return new Secret($data['id'], $data['created_at'], $data['_links']);
    }

    /**
     * Create a new account secret
     */
    public function createSecret(string $apiKey, string $newSecret) : Secret
    {
        $api = clone $this->secretsAPI;
        $api->setBaseUrl($api->getClient()->getApiUrl());
        $api->setBaseUri('/accounts/' . $apiKey . '/secrets');

        try {
            $response = $api->create(['secret' => $newSecret]);
        } catch (ExceptionRequest $e) {
            // @deprectated Throw a Validation exception to preserve old behavior
            // This will change to a general Request exception in the future
            $rawResponse = json_decode(@$e->getResponse()->getBody()->getContents(), true);
            if (array_key_exists('invalid_parameters', $rawResponse)) {
                throw new Validation($e->getMessage(), $e->getCode(), null, $rawResponse['invalid_parameters']);
            }
            throw $e;
        }

        return new Secret($response['id'], $response['created_at'], $response['_links']);
    }

    public function deleteSecret(string $apiKey, string $secretId) : void
    {
        $api = clone $this->secretsAPI;
        $api->setBaseUrl($api->getClient()->getApiUrl());
        $api->setBaseUri('/accounts/' . $apiKey . '/secrets');
        $api->delete($secretId);
    }
}
