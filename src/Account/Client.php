<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Account;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\ClientAwareInterface;
use Vonage\Client\ClientAwareTrait;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Request as ClientRequestException;
use Vonage\Client\Exception\Validation as ClientValidationException;
use Vonage\Entity\Filter\KeyValueFilter;
use Vonage\InvalidResponseException;

use function array_key_exists;
use function count;
use function is_null;
use function json_decode;

/**
 * @todo Unify the exception handling to avoid duplicated code and logic (ie: getPrefixPricing())
 */
class Client implements ClientAwareInterface, APIClient
{
    /**
     * @deprecated This object will be dropping support for ClientAwareInterface in the future
     */
    use ClientAwareTrait;

    /**
     * @var APIResource
     */
    protected $accountAPI;

    /**
     * @var APIResource
     */
    protected $secretsAPI;

    public function __construct(?APIResource $accountAPI = null, ?APIResource $secretsAPI = null)
    {
        $this->accountAPI = $accountAPI;
        $this->secretsAPI = $secretsAPI;
    }

    /**
     * Shim to handle older instantiations of this class
     *
     * @deprecated Will remove in v3
     */
    public function getAccountAPI(): APIResource
    {
        if (is_null($this->accountAPI)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUrl($this->getClient()->getRestUrl())
                ->setIsHAL(false)
                ->setBaseUri('/account')
                ->setCollectionName('');
            $this->accountAPI = $api;
        }

        return clone $this->accountAPI;
    }

    public function getAPIResource(): APIResource
    {
        return $this->getAccountAPI();
    }

    /**
     * Shim to handle older instantiations of this class
     *
     * @deprecated Will remove in v3
     */
    public function getSecretsAPI(): APIResource
    {
        if (is_null($this->secretsAPI)) {
            $api = new APIResource();
            $api->setClient($this->getClient())
                ->setBaseUrl($this->getClient()->getApiUrl())
                ->setIsHAL(false)
                ->setBaseUri('/accounts')
                ->setCollectionName('');
            $this->secretsAPI = $api;
        }

        return clone $this->secretsAPI;
    }

    /**
     * Returns pricing based on the prefix requested
     *
     * @return array<PrefixPrice>
     */
    public function getPrefixPricing($prefix): array
    {
        $api = $this->getAccountAPI();
        $api->setBaseUri('/account/get-prefix-pricing/outbound');
        $api->setCollectionName('prices');

        $data = $api->search(new KeyValueFilter(['prefix' => $prefix]));

        if (count($data) === 0) {
            return [];
        }

        // Multiple countries can match each prefix
        $prices = [];

        foreach ($data as $p) {
            $prefixPrice = new PrefixPrice();
            $prefixPrice->fromArray($p);
            $prices[] = $prefixPrice;
        }

        return $prices;
    }

    /**
     * Get SMS Pricing based on Country
     *
     * @throws ClientExceptionInterface
     * @throws ClientRequestException
     * @throws ClientException\Exception
     * @throws ClientException\Server
     */
    public function getSmsPrice(string $country): SmsPrice
    {
        $body = $this->makePricingRequest($country, 'sms');
        $smsPrice = new SmsPrice();
        $smsPrice->fromArray($body);

        return $smsPrice;
    }

    /**
     * Get Voice pricing based on Country
     *
     * @throws ClientExceptionInterface
     * @throws ClientRequestException
     * @throws ClientException\Exception
     * @throws ClientException\Server
     */
    public function getVoicePrice(string $country): VoicePrice
    {
        $body = $this->makePricingRequest($country, 'voice');
        $voicePrice = new VoicePrice();
        $voicePrice->fromArray($body);

        return $voicePrice;
    }

    /**
     * @throws ClientRequestException
     * @throws ClientException\Exception
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     *
     * @todo This should return an empty result instead of throwing an Exception on no results
     */
    protected function makePricingRequest($country, $pricingType): array
    {
        $api = $this->getAccountAPI();
        $api->setBaseUri('/account/get-pricing/outbound/' . $pricingType);
        $results = $api->search(new KeyValueFilter(['country' => $country]));
        $data = $results->getPageData();

        if (is_null($data)) {
            throw new ClientException\Server('No results found');
        }

        return $data;
    }

    /**
     * Gets the accounts current balance in Euros
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     *
     * @todo This needs further investigated to see if '' can even be returned from this endpoint
     */
    public function getBalance(): Balance
    {
        $data = $this->getAccountAPI()->get('get-balance', [], ['accept' => 'application/json']);

        if (is_null($data)) {
            throw new ClientException\Server('No results found');
        }

        return new Balance($data['value'], $data['autoReload']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function topUp($trx): void
    {
        $api = $this->getAccountAPI();
        $api->setBaseUri('/account/top-up');
        $api->submit(['trx' => $trx]);
    }

    /**
     * Return the account settings
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     */
    public function getConfig(): Config
    {
        $api = $this->getAccountAPI();
        $api->setBaseUri('/account/settings');
        $body = $api->submit();

        if ($body === '') {
            throw new ClientException\Server('Response was empty');
        }

        $body = json_decode($body, true);

        return new Config(
            $body['mo-callback-url'],
            $body['dr-callback-url'],
            $body['max-outbound-request'],
            $body['max-inbound-request'],
            $body['max-calls-per-second']
        );
    }

    /**
     * Update account config
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\Server
     */
    public function updateConfig(array $options): Config
    {
        // supported options are SMS Callback and DR Callback
        $params = [];

        if (isset($options['sms_callback_url'])) {
            $params['moCallBackUrl'] = $options['sms_callback_url'];
        }

        if (isset($options['dr_callback_url'])) {
            $params['drCallBackUrl'] = $options['dr_callback_url'];
        }

        $api = $this->getAccountAPI();
        $api->setBaseUri('/account/settings');

        $rawBody = $api->submit($params);

        if ($rawBody === '') {
            throw new ClientException\Server('Response was empty');
        }

        $body = json_decode($rawBody, true);

        return new Config(
            $body['mo-callback-url'],
            $body['dr-callback-url'],
            $body['max-outbound-request'],
            $body['max-inbound-request'],
            $body['max-calls-per-second']
        );
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws InvalidResponseException
     */
    public function listSecrets(string $apiKey): SecretCollection
    {
        $api = $this->getSecretsAPI();

        $data = $api->get($apiKey . '/secrets');
        return new SecretCollection($data['_embedded']['secrets'], $data['_links']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws InvalidResponseException
     */
    public function getSecret(string $apiKey, string $secretId): Secret
    {
        $api = $this->getSecretsAPI();

        $data = $api->get($apiKey . '/secrets/' . $secretId);
        return new Secret($data);
    }

    /**
     * Create a new account secret
     *
     * @throws ClientExceptionInterface
     * @throws ClientRequestException
     * @throws ClientException\Exception
     * @throws InvalidResponseException
     * @throws ClientValidationException
     */
    public function createSecret(string $apiKey, string $newSecret): Secret
    {
        $api = $this->getSecretsAPI();
        $api->setBaseUri('/accounts/' . $apiKey . '/secrets');

        try {
            $response = $api->create(['secret' => $newSecret]);
        } catch (ClientRequestException $e) {
            // @deprecated Throw a Validation exception to preserve old behavior
            // This will change to a general Request exception in the future
            $rawResponse = json_decode(@$e->getResponse()->getBody()->getContents(), true);

            if (array_key_exists('invalid_parameters', $rawResponse)) {
                throw new ClientValidationException(
                    $e->getMessage(),
                    $e->getCode(),
                    null,
                    $rawResponse['invalid_parameters']
                );
            }

            throw $e;
        }

        return new Secret($response);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function deleteSecret(string $apiKey, string $secretId): void
    {
        $api = $this->getSecretsAPI();
        $api->setBaseUri('/accounts/' . $apiKey . '/secrets');
        $api->delete($secretId);
    }
}
