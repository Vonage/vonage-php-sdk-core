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
use Vonage\Client\APIResource;
use Vonage\Client\Exception as ClientException;
use Vonage\Client\Exception\Request as ClientRequestException;
use Vonage\Entity\Filter\KeyValueFilter;

use function count;
use function is_null;
use function json_decode;

class Client
{
    /**
     * @var APIResource
     */
    protected $accountAPI;

    public function __construct(?APIResource $accountAPI = null)
    {
        $this->accountAPI = $accountAPI;
    }

    /**
     * Returns pricing based on the prefix requested
     *
     * @return array<PrefixPrice>
     */
    public function getPrefixPricing($prefix): array
    {
        $this->accountAPI->setBaseUri('/account/get-prefix-pricing/outbound');
        $this->accountAPI->setCollectionName('prices');

        $data = $this->accountAPI->search(new KeyValueFilter(['prefix' => $prefix]));

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
        $this->accountAPI->setBaseUri('/account/get-pricing/outbound/' . $pricingType);
        $results = $this->accountAPI->search(new KeyValueFilter(['country' => $country]));
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
        $data = $this->accountAPI->get('get-balance', [], ['accept' => 'application/json']);

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
        $this->accountAPI->setBaseUri('/account/top-up');
        $this->accountAPI->submit(['trx' => $trx]);
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
        $this->accountAPI->setBaseUri('/account/settings');
        $body = $this->accountAPI->submit();

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

        $this->accountAPI->setBaseUri('/account/settings');

        $rawBody = $this->accountAPI->submit($params);

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
}
