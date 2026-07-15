<?php

declare(strict_types=1);

namespace Vonage\Account;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\RequestException as ClientRequestException;
use Vonage\Entity\Filter\KeyValueFilter;

use function count;
use function is_null;
use function json_decode;

/**
 * @todo Unify the exception handling to avoid duplicated code and logic (ie: getPrefixPricing())
 */
class Client implements APIClient
{
    public function __construct(protected ?APIResource $accountAPI = null)
    {
    }

    /**
     * @deprecated This method will be removed in the next major version.
     *             The APIResource is injected and should not be accessed directly from outside the client.
     */
    public function getAPIResource(): APIResource
    {
        trigger_error(
            'Vonage\\Account\\Client::getAPIResource() is deprecated and will be removed in the next major version.',
            E_USER_DEPRECATED
        );
        return clone $this->accountAPI;
    }

    /**
     * Returns pricing based on the prefix requested
     *
     * @return array<PrefixPrice>
     */
    public function getPrefixPricing(string $prefix): array
    {
        $api = clone $this->accountAPI;
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
     * @throws ClientException\ServerException
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
     * @throws ClientException\ServerException
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
     * @throws ClientException\ServerException
     * @throws ClientExceptionInterface
     *
     * @todo This should return an empty result instead of throwing an Exception on no results
     */
    protected function makePricingRequest($country, $pricingType): array
    {
        $api = clone $this->accountAPI;
        $api->setBaseUri('/account/get-pricing/outbound/' . $pricingType);
        $results = $api->search(new KeyValueFilter(['country' => $country]));
        $pageData = $results->getPageData();

        if (is_null($pageData)) {
            throw new ClientException\ServerException('No results found');
        }

        return $pageData;
    }

    /**
     * Gets the accounts current balance in Euros
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\ServerException
     *
     * @todo This needs further investigated to see if '' can even be returned from this endpoint
     */
    public function getBalance(): Balance
    {
        $data = $this->accountAPI->get('get-balance', [], ['accept' => 'application/json']);

        if (is_null($data)) {
            throw new ClientException\ServerException('No results found');
        }

        return new Balance($data['value'], $data['autoReload']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     */
    public function topUp(string $trx): void
    {
        $api = clone $this->accountAPI;
        $api->setBaseUri('/account/top-up');
        $api->submit(['trx' => $trx]);
    }

    /**
     * Return the account settings
     *
     * @throws ClientExceptionInterface
     * @throws ClientException\Exception
     * @throws ClientException\ServerException
     */
    public function getConfig(): Config
    {
        $api = clone $this->accountAPI;
        $api->setBaseUri('/account/settings');
        $body = $api->submit();

        if ($body === '') {
            throw new ClientException\ServerException('Response was empty');
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
     * @throws ClientException\ServerException
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

        $api = clone $this->accountAPI;
        $api->setBaseUri('/account/settings');

        $rawBody = $api->submit($params);

        if ($rawBody === '') {
            throw new ClientException\ServerException('Response was empty');
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
}
