<?php

declare(strict_types=1);

namespace Vonage\Subaccount;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Subaccount\SubaccountObjects\Account;

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

    public function getSubaccounts(string $apiKey): array
    {
        $api = clone $this->api;
        $api->setCollectionName('subaccounts');

        $collection = $this->api->search(null, '/' . $apiKey . '/subaccounts');
        $collection->setNoQueryParameters(true);

        $hydrator = new Hydrator();
        $subaccounts = $collection->getPageData()['_embedded'][$api->getCollectionName()];

        return array_map(function ($item) use ($hydrator) {
            return $hydrator->hydrate($item);
        }, $subaccounts);
    }
}
