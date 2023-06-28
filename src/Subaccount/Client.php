<?php

declare(strict_types=1);

namespace Vonage\Subaccount;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Entity\Filter\EmptyFilter;
use Vonage\Entity\Filter\FilterInterface;
use Vonage\Entity\Hydrator\ArrayHydrator;
use Vonage\Subaccount\Request\NumberTransferRequest;
use Vonage\Subaccount\Request\TransferBalanceRequest;
use Vonage\Subaccount\Request\TransferCreditRequest;
use Vonage\Subaccount\SubaccountObjects\Account;
use Vonage\Subaccount\SubaccountObjects\BalanceTransfer;
use Vonage\Subaccount\SubaccountObjects\CreditTransfer;

class Client implements APIClient
{
    public const PRIMARY_ACCOUNT_ARRAY_KEY = 'primary_account';

    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function getPrimaryAccount(string $apiKey): Account
    {
        $response = $this->api->get($apiKey . '/subaccounts');

        return (new Account())->fromArray($response['_embedded'][self::PRIMARY_ACCOUNT_ARRAY_KEY]);
    }

    public function getSubaccount(string $apiKey, string $subaccountApiKey): Account
    {
        $response = $this->api->get($apiKey . '/subaccounts/' . $subaccountApiKey);
        return (new Account())->fromArray($response);
    }

    public function getSubaccounts(string $apiKey): array
    {
        $api = clone $this->api;
        $api->setCollectionName('subaccounts');

        $collection = $this->api->search(null, '/' . $apiKey . '/subaccounts');
        $collection->setNoQueryParameters(true);

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new Account());
        $subaccounts = $collection->getPageData()['_embedded'][$api->getCollectionName()];

        return array_map(function ($item) use ($hydrator) {
            return $hydrator->hydrate($item);
        }, $subaccounts);
    }

    public function createSubaccount(string $apiKey, Account $account): ?array
    {
        return $this->api->create($account->toArray(), '/' . $apiKey . '/subaccounts');
    }

    public function makeBalanceTransfer(TransferBalanceRequest $transferRequest): BalanceTransfer
    {
        $response = $this->api->create($transferRequest->toArray(), '/' . $transferRequest->getApiKey() . '/balance-transfers');

        return (new BalanceTransfer())->fromArray($response);
    }

    public function makeCreditTransfer(TransferCreditRequest $transferRequest): CreditTransfer
    {
        $response = $this->api->create($transferRequest->toArray(), '/' . $transferRequest->getApiKey() . '/credit-transfers');
        return (new CreditTransfer())->fromArray($response);
    }

    public function updateSubaccount(string $apiKey, string $subaccountApiKey, Account $account): ?array
    {
        return $this->api->partiallyUpdate($apiKey . '/subaccounts/' . $subaccountApiKey, $account->toArray());
    }

    public function getCreditTransfers(string $apiKey, FilterInterface $filter = null): mixed
    {
        if (!$filter) {
            $filter = new EmptyFilter();
        }

        $response = $this->api->get($apiKey . '/credit-transfers', $filter->getQuery());

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new CreditTransfer());
        $transfers = $response['_embedded']['credit_transfers'];

        return array_map(function ($item) use ($hydrator) {
            return $hydrator->hydrate($item);
        }, $transfers);
    }

    public function getBalanceTransfers(string $apiKey, FilterInterface $filter = null): mixed
    {
        if (!$filter) {
            $filter = new EmptyFilter();
        }

        $response = $this->api->get($apiKey . '/balance-transfers', $filter->getQuery());

        $hydrator = new ArrayHydrator();
        $hydrator->setPrototype(new BalanceTransfer());
        $transfers = $response['_embedded']['balance_transfers'];

        return array_map(function ($item) use ($hydrator) {
            return $hydrator->hydrate($item);
        }, $transfers);
    }

    public function makeNumberTransfer(NumberTransferRequest $request): ?array
    {
        return $this->api->create($request->toArray(), '/' . $request->getApiKey() . '/transfer-number');
    }
}
