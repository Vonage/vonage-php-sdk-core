<?php

namespace Vonage\ProactiveConnect;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Entity\IterableAPICollection;
use Vonage\ProactiveConnect\Request\ListBaseObject;
use Vonage\ProactiveConnect\Request\ManualList;

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

    public function createList(ListBaseObject $request): ?array
    {
        return $this->api->create($request->toArray(), '/lists');
    }

    public function getListById(string $id)
    {
        return $this->api->get('lists/' . $id);
    }

    public function updateList(string $id, ListBaseObject $request): ?array
    {
        return $this->api->update('lists/' . $id, $request->toArray());
    }

    public function deleteList(string $id): ?array
    {
        return $this->api->delete('lists/' . $id);
    }

    public function clearListItemsById(string $id): ?array
    {
        return $this->api->create([], '/lists/' . $id . '/clear');
    }

    public function fetchListItemsById(string $id): ?array
    {
        return $this->api->create([], '/lists/' . $id . '/fetch');
    }
}
