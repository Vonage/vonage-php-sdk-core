<?php

namespace Vonage\ProactiveConnect;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Entity\IterableAPICollection;
use Vonage\ProactiveConnect\Request\CreateListRequest;
use Vonage\ProactiveConnect\Request\CreateManualListRequest;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    public function getLists(?int $page = null, ?int $pageSize = null): IterableAPICollection
    {
        $lists = $this->api->search(null, '/lists');

        $lists->setPageIndexKey('page');

        if ($page) {
            $lists->setPage($page);
        }

        if ($pageSize) {
            $lists->setSize($pageSize);
        }

        // This API has the potential to have a lot of data. Defaulting to
        // Auto advance off, you can override in the return object
        $lists->setAutoAdvance(false);

        return $lists;
    }

    public function createList(CreateListRequest $request): ?array
    {
        return $this->api->create($request->toArray(), '/lists');
    }

    public function getListById(string $id)
    {
        return $this->api->get('lists/' . $id);
    }
}
