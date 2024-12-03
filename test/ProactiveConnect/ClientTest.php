<?php

declare(strict_types=1);

namespace VonageTest\ProactiveConnect;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Entity\IterableAPICollection;
use Vonage\ProactiveConnect\Client as ProactiveConnectClient;
use Vonage\ProactiveConnect\Objects\ListItem;
use Vonage\ProactiveConnect\Objects\ManualList;
use Vonage\ProactiveConnect\Objects\SalesforceList;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    protected ObjectProphecy $vonageClient;
    protected ProactiveConnectClient $proactiveConnectClient;
    protected APIResource $api;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/Fixtures/Responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://api-eu.vonage.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(
                new Client\Credentials\Keypair(
                    file_get_contents(__DIR__ . '/test.key'),
                    'c90ddd99-9a5d-455f-8ade-dde4859e590e',
                )
            )
        );

        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource())
            ->setIsHAL(true)
            ->setErrorsOn200(false)
            ->setClient($this->vonageClient->reveal())
            ->setAuthHandlers(new Client\Credentials\Handler\KeypairHandler())
            ->setBaseUrl('https://api-eu.vonage.com/v0.1/bulk');

        $this->proactiveConnectClient = @new ProactiveConnectClient($this->api);
    }

    public function testHasSetupClientCorrectly(): void
    {
        $this->assertInstanceOf(ProactiveConnectClient::class, $this->proactiveConnectClient);
    }

    public function testSetsRequestAuthCorrectly(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists?page=1',
                $uriString
            );

            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            return true;
        }))->willReturn($this->getResponse('list-success'));

        $list = @$this->proactiveConnectClient->getLists();
        $this->assertInstanceOf(IterableAPICollection::class, $list);
        $list->getPageData();
    }

    public function testListUrlEndpoint(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists?page=1',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('list-success'));

        $list = @$this->proactiveConnectClient->getLists();
        $this->assertInstanceOf(IterableAPICollection::class, $list);
        @$list->getPageData();
    }

    public function testCanGetList(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists?page=1',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('list-success'));

        $list = $this->proactiveConnectClient->getLists();
        $this->assertInstanceOf(IterableAPICollection::class, $list);

        $payload = [];

        foreach ($list as $listItem) {
            $payload[] = $listItem;
        }

        $pageMeta = $list->getPageData();
        $this->assertEquals(1, $pageMeta['page']);
        $this->assertEquals(2, $pageMeta['total_items']);
        $this->assertEquals(100, $pageMeta['page_size']);

        $this->assertCount(2, $payload);
        $this->assertEquals('Recipients for demo', $payload[0]['name']);
        $this->assertEquals('Salesforce contacts', $payload[1]['name']);
    }

    public function testCanGetListWithPage(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists?page_size=50&page=3',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('list-custom-page-success'));

        $list = $this->proactiveConnectClient->getLists(3, 50);
        $this->assertInstanceOf(IterableAPICollection::class, $list);

        $pageMeta = $list->getPageData();
        $this->assertEquals(3, $pageMeta['page']);
        $this->assertEquals(2, $pageMeta['total_items']);
        $this->assertEquals(50, $pageMeta['page_size']);
    }

    public function testGetListFailsAuth(): void
    {
        $this->expectException(Client\Exception\Request::class);
        $this->expectExceptionMessage('Unauthorized');

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists?page_size=50&page=3',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('list-failed-auth', 401));

        $list = $this->proactiveConnectClient->getLists(3, 50);
        $list->getPageData();
    }

    public function testCanCreateManualList(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestJsonBodyContains('name', 'my-list', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('list-create-success', 201));

        $newListRequest = new ManualList('my-list');

        $response = $this->proactiveConnectClient->createList(
            $newListRequest
        );

        $this->assertEquals('my-list', $response['name']);
    }

    public function testCanCreateManualListWithSetters(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestJsonBodyContains('name', 'my-list', $request);
            $this->assertRequestJsonBodyContains('description', 'my-description', $request);
            $this->assertRequestJsonBodyContains('tags', ['tag1', 'tag2'], $request);
            $this->assertRequestJsonBodyContains('name', 'phone_number', $request, true);
            $this->assertRequestJsonBodyContains('alias', 'phone', $request, true);
            $this->assertRequestJsonBodyContains('key', false, $request, true);

            return true;
        }))->willReturn($this->getResponse('list-create-success', 201));

        $newListRequest = new ManualList('my-list');
        $newListRequest->setDescription('my-description')
            ->setTags(['tag1', 'tag2'])
            ->setAttributes([
                [
                    'name' => 'phone_number',
                    'alias' => 'phone',
                    'key' => false
                ]
            ]);

        $response = $this->proactiveConnectClient->createList(
            $newListRequest
        );

        $this->assertEquals('my-list', $response['name']);
    }

    public function testCanCreateSalesforceList(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestJsonBodyContains('name', 'my-list', $request);
            $this->assertRequestJsonBodyContains('description', 'my-description', $request);
            $this->assertRequestJsonBodyContains('tags', ['tag1', 'tag2'], $request);
            $this->assertRequestJsonBodyContains('name', 'phone_number', $request, true);
            $this->assertRequestJsonBodyContains('alias', 'phone', $request, true);
            $this->assertRequestJsonBodyContains('key', false, $request, true);
            $this->assertRequestJsonBodyContains('type', 'salesforce', $request, true);
            $this->assertRequestJsonBodyContains('integration_id', 'salesforce_credentials', $request, true);
            $this->assertRequestJsonBodyContains('soql', 'select Id, LastName, FirstName, Phone, Email FROM Contact', $request, true);

            return true;
        }))->willReturn($this->getResponse('list-create-success', 201));

        $createSalesforceListRequest = new SalesforceList('my-list');
        $createSalesforceListRequest->setDescription('my-description')
           ->setTags(['tag1', 'tag2'])
           ->setSalesforceIntegrationId('salesforce_credentials')
           ->setSalesforceSoql('select Id, LastName, FirstName, Phone, Email FROM Contact')
           ->setAttributes([
               [
                   'name' => 'phone_number',
                   'alias' => 'phone',
                   'key' => false
               ]
           ]);

        $response = $this->proactiveConnectClient->createList(
            $createSalesforceListRequest
        );

        $this->assertEquals('my-list', $response['name']);
    }

    public function testCannotCreateSalesforceListWithoutIntegrationId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('integration_id needs to be set on datasource on a Salesforce list');

        $this->vonageClient->send(Argument::that(fn (Request $request) => true))->willReturn($this->getResponse('list-create-success'));

        $createSalesforceListRequest = new SalesforceList('my-list');
        $createSalesforceListRequest->setDescription('my-description')
            ->setSalesforceSoql('select Id, LastName, FirstName, Phone, Email FROM Contact')
            ->setAttributes([
                [
                    'name' => 'phone_number',
                    'alias' => 'phone',
                    'key' => false
                ]
            ]);

        $response = $this->proactiveConnectClient->createList(
            $createSalesforceListRequest
        );
    }

    public function testCannotCreateSalesforceListWithoutSoql(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('soql needs to be set on datasource on a Salesforce list');

        $this->vonageClient->send(Argument::that(fn (Request $request) => true))->willReturn($this->getResponse('list-create-success'));

        $createSalesforceListRequest = new SalesforceList('my-list');
        $createSalesforceListRequest->setDescription('my-description')
            ->setSalesforceIntegrationId('test-string')
            ->setAttributes([
                [
                    'name' => 'phone_number',
                    'alias' => 'phone',
                    'key' => false
                ]
            ]);

        $response = $this->proactiveConnectClient->createList(
            $createSalesforceListRequest
        );
    }

    public function testCanGetListById(): void
    {
        $id = '29192c4a-4058-49da-86c2-3e349d1065b7';

        $this->vonageClient->send(Argument::that(function (Request $request) use ($id) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/' . $id,
                $uriString
            );

            $this->assertRequestMethod('GET', $request);
            return true;
        }))->willReturn($this->getResponse('list-get-success'));

        $response = $this->proactiveConnectClient->getListById(
            $id
        );

        $this->assertEquals('list name', $response['name']);
        $this->assertEquals('list description', $response['description']);
    }

    public function testCanUpdateSalesforceList(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('PUT', $request->getMethod());
            $this->assertRequestJsonBodyContains('name', 'my name', $request);
            $this->assertRequestJsonBodyContains('description', 'my description', $request);
            $this->assertRequestJsonBodyContains('tags', ['tag1', 'tag2'], $request);
            $this->assertRequestJsonBodyContains('name', 'phone_number', $request, true);
            $this->assertRequestJsonBodyContains('alias', 'phone', $request, true);
            $this->assertRequestJsonBodyContains('key', false, $request, true);
            $this->assertRequestJsonBodyContains('type', 'salesforce', $request, true);
            $this->assertRequestJsonBodyContains('integration_id', 'salesforce_credentials', $request, true);
            $this->assertRequestJsonBodyContains('soql', 'select Id, LastName, FirstName, Phone, Email FROM Contact', $request, true);

            return true;
        }))->willReturn($this->getResponse('list-update-success'));

        $id = '29192c4a-4058-49da-86c2-3e349d1065b7';

        $salesforceList = new SalesforceList('my name');
        $salesforceList->setDescription('my description')
            ->setTags(['tag1', 'tag2'])
            ->setSalesforceIntegrationId('salesforce_credentials')
            ->setSalesforceSoql('select Id, LastName, FirstName, Phone, Email FROM Contact')
            ->setAttributes([
                [
                    'name' => 'phone_number',
                    'alias' => 'phone',
                    'key' => false
                ]
            ]);

        $response = $this->proactiveConnectClient->updateList(
            $id,
            $salesforceList
        );

        $this->assertEquals('my name', $response['name']);
        $this->assertEquals($id, $response['id']);
    }

    public function testCanUpdateManualList(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('PUT', $request->getMethod());
            $this->assertRequestJsonBodyContains('name', 'my name', $request);
            $this->assertRequestJsonBodyContains('description', 'my description', $request);
            $this->assertRequestJsonBodyContains('tags', ['tag1', 'tag2'], $request);
            $this->assertRequestJsonBodyContains('name', 'phone_number', $request, true);
            $this->assertRequestJsonBodyContains('alias', 'phone', $request, true);
            $this->assertRequestJsonBodyContains('key', false, $request, true);

            return true;
        }))->willReturn($this->getResponse('list-update-success'));

        $id = '29192c4a-4058-49da-86c2-3e349d1065b7';
        $manualList = new ManualList('my name');
        $manualList->setDescription('my description')
                       ->setTags(['tag1', 'tag2'])
                       ->setAttributes([
                           [
                               'name' => 'phone_number',
                               'alias' => 'phone',
                               'key' => false
                           ]
                       ]);

        $response = $this->proactiveConnectClient->updateList(
            $id,
            $manualList
        );

        $this->assertEquals('my name', $response['name']);
        $this->assertEquals($id, $response['id']);
    }

    public function testCanDeleteList(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('DELETE', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/29192c4a-4058-49da-86c2-3e349d1065b7',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('list-delete-success'));

        $id = '29192c4a-4058-49da-86c2-3e349d1065b7';

        $response = $this->proactiveConnectClient->deleteList(
            $id,
        );
    }

    public function testWillClearListItemsById()
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/29192c4a-4058-49da-86c2-3e349d1065b7/clear',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('list-clear-success', 202));

        $id = '29192c4a-4058-49da-86c2-3e349d1065b7';

        $response = $this->proactiveConnectClient->clearListItemsById(
            $id,
        );
    }

    public function testWillReplaceFetchItemsFromDataSource()
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/29192c4a-4058-49da-86c2-3e349d1065b7/fetch',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('list-fetch-success', 202));

        $id = '29192c4a-4058-49da-86c2-3e349d1065b7';

        $response = $this->proactiveConnectClient->fetchListItemsById(
            $id,
        );
    }

    public function testWillGetListItems(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/29192c4a-4058-49da-86c2-3e349d1065b7/items?page=1',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('item-list-success'));

        $id = '29192c4a-4058-49da-86c2-3e349d1065b7';

        $response = $this->proactiveConnectClient->getItemsByListId(
            $id,
        );

        $testPayload = [];

        foreach ($response as $item) {
            $testPayload[] = $item;
        }

        $this->assertEquals('6e26d247-e074-4f68-b72b-dd92aa02c7e0', $testPayload[0]['id']);
        $this->assertEquals('f7c029ad-93c3-469c-9267-73c3c6864161', $testPayload[1]['id']);
    }

    public function testWillCreateListItem(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());
            $this->assertRequestJsonBodyContains('firstName', 'Adrianna', $request, true);
            $this->assertRequestJsonBodyContains('lastName', 'Campbell', $request, true);
            $this->assertRequestJsonBodyContains('phone', '155550067383', $request, true);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/6e26d247-e074-4f68-b72b-dd92aa02c7e0/items',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('item-create-success', 201));

        $id = '6e26d247-e074-4f68-b72b-dd92aa02c7e0';

        $listItem = new ListItem([
            'firstName' => 'Adrianna',
            'lastName' => 'Campbell',
            'phone' => '155550067383'
        ]);

        $response = $this->proactiveConnectClient->createItemOnListId(
            $id,
            $listItem
        );

        $this->assertEquals('29192c4a-4058-49da-86c2-3e349d1065b7', $response['id']);
        $this->assertEquals('Adrianna', $response['data']['firstName']);
    }

    public function testWillDownloadItemCsv(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/6e26d247-e074-4f68-b72b-dd92aa02c7e0/items/download',
                $uriString
            );

            return true;
        }))->willReturn($this->getCSVResponse());

        $id = '6e26d247-e074-4f68-b72b-dd92aa02c7e0';

        $response = $this->proactiveConnectClient->getListCsvFileByListId($id);

        $response->rewind();

        $csvArray = [];
        $header = null;

        $handle = tmpfile();
        fwrite($handle, (string) $response->getContents());
        fseek($handle, 0);

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (!$header) {
                $header = $row;
            } else {
                $csvArray[] = array_combine($header, $row);
            }
        }

        fclose($handle);

        $this->assertIsArray($csvArray);
        $this->assertEquals('551546578', $csvArray[0]['phone']);
        $this->assertEquals('Campbell', $csvArray[1]['lastName']);
        $this->assertEquals('Jane', $csvArray[2]['firstName']);
        $this->assertInstanceOf(Stream::class, $response);
    }

    public function testWillGetItemById(): void
    {
        $listId = '29192c4a-4058-49da-86c2-3e349d1065b7';
        $itemId = '4cb98f71-a879-49f7-b5cf-2314353eb52c';

        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertRequestMethod('GET', $request);
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/29192c4a-4058-49da-86c2-3e349d1065b7/items/4cb98f71-a879-49f7-b5cf-2314353eb52c',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('item-get-success'));

        $response = $this->proactiveConnectClient->getItemByIdandListId(
            $itemId,
            $listId
        );

        $this->assertEquals($itemId, $response['id']);
        $this->assertEquals($listId, $response['list_id']);
    }

    public function testWillUpdateItem(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('PUT', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('item-update-success'));

        $itemId = '29192c4a-4058-49da-86c2-3e349d1065b7';
        $listId = '4cb98f71-a879-49f7-b5cf-2314353eb52c';

        $listItem = new ListItem([
            'firstName' => 'Linda',
            'lastName' => 'Smith',
            'phone' => '2365236235'
        ]);

        $listItem->set('phone', '876484843');

        $response = $this->proactiveConnectClient->updateItemByIdAndListId(
            $itemId,
            $listId,
            $listItem
        );
    }

    public function testWillDeleteItemOffList(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('DELETE', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/4cb98f71-a879-49f7-b5cf-2314353eb52c/items/29192c4a-4058-49da-86c2-3e349d1065b7',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('item-delete-success', 204));

        $itemId = '29192c4a-4058-49da-86c2-3e349d1065b7';
        $listId = '4cb98f71-a879-49f7-b5cf-2314353eb52c';

        $response = $this->proactiveConnectClient->deleteItemByIdAndListId(
            $itemId,
            $listId,
        );
    }

    public function testWillImportItemsFromCsv(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals('POST', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/lists/4cb98f71-a879-49f7-b5cf-2314353eb52c/items/import',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('item-upload-success'));

        $listId = '4cb98f71-a879-49f7-b5cf-2314353eb52c';
        $filename = __DIR__ . '/Fixtures/Payload/testUpload.csv';

        $response = $this->proactiveConnectClient->uploadCsvToList(
            $filename,
            $listId,
        );
    }

    public function testWillFindEvents(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/events?page=1',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('events-get-success'));

        $events = $this->proactiveConnectClient->getEvents();
        $this->assertInstanceOf(IterableAPICollection::class, $events);

        $payload = [];

        foreach ($events as $event) {
            $payload[] = $event;
        }

        $this->assertCount(2, $payload);
    }

    public function testWillFindEventsByPageAndPageSize(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v0.1/bulk/events?page_size=40&page=2',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('events-filter-get-success'));

        $events = $this->proactiveConnectClient->getEvents(2, 40);

        $payload = [];

        foreach ($events as $event) {
            $payload[] = $event;
        }

        $this->assertCount(3, $payload);

        $pageMeta = $events->getPageData();
        $this->assertEquals(2, $pageMeta['page']);
        $this->assertEquals(3, $pageMeta['total_items']);
        $this->assertEquals(40, $pageMeta['page_size']);
    }

    protected function getCSVResponse(): Response
    {
        $csvContent = "firstName,lastName,phone\nJames,Smith,551546578\nAdrianna,Campbell,551545778\nJane,Doe,551457578";
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($csvContent);
        $stream->rewind();

        return new Response(
            $stream,
            200,
            ['Content-Type' => 'text/csv; charset=utf-8']
        );
    }
}
