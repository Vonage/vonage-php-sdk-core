<?php

namespace Vonage\ProactiveConnect;

use Laminas\Diactoros\Stream;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Entity\IterableAPICollection;
use Vonage\ProactiveConnect\Objects\ListBaseObject;
use Vonage\ProactiveConnect\Objects\ListItem;

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
        $this->api->setCollectionName('lists');
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

    public function getItemsByListId(string $id, ?int $page = null, ?int $pageSize = null): IterableAPICollection
    {
        $this->api->setCollectionName('items');
        $lists = $this->api->search(null, '/lists/' . $id . '/items');

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

    public function createItemOnListId(string $id, ListItem $listItem): ?array
    {
        return $this->api->create($listItem->toArray(), '/lists/' . $id . '/items');
    }

    public function getListCsvFileByListId(string $id): mixed
    {
        return $this->api->get('lists/' . $id . '/items/download', [], ['Content-Type' => 'text/csv'], false);
    }

    public function getItemByIdandListId(string $itemId, string $listId)
    {
        return $this->api->get('lists/' . $listId . '/items/' . $itemId);
    }

    public function updateItemByIdAndListId(string $itemId, string $listId, ListItem $listItem): ?array
    {
        return $this->api->update('/lists' . $listId . '/items/' . $itemId, $listItem->toArray());
    }

    public function deleteItemByIdAndListId(string $itemId, string $listId): ?array
    {
        return $this->api->delete('lists/' . $listId . '/items/' . $itemId);
    }

    public function uploadCsvToList(string $filename, string $listId)
    {
        $stream = new Stream(fopen($filename, 'r'));

        $multipart = [
            [
                'name' => 'file',
                'contents' => $stream,
                'filename' => basename($filename)
            ]
        ];

        return $this->api->create(
            [$multipart],
            '/lists/' . $listId . '/items/import',
            ['Content-Type' => 'multipart/form-data']
        );
    }

    public function getEvents(?int $page = null, ?int $pageSize = null)
    {
        $this->api->setCollectionName('events');
        $lists = $this->api->search(null, '/events');

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
}
