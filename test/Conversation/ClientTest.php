<?php

declare(strict_types=1);

namespace VonageTest\Conversation;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Conversation\Client as ConversationClient;
use Vonage\Conversation\ConversationObjects\Conversation;
use Vonage\Conversation\Filter\ListConversationFilter;
use Vonage\Entity\IterableAPICollection;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    protected ObjectProphecy $vonageClient;
    protected ConversationClient $conversationsClient;
    protected APIResource $api;
    protected int $requestIndex = 0;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://api.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Keypair(
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def'
            ))
        );

        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource())
            ->setIsHAL(true)
            ->setCollectionName('conversations')
            ->setErrorsOn200(false)
            ->setClient($this->vonageClient->reveal())
            ->setAuthHandler(new Client\Credentials\Handler\KeypairHandler())
            ->setBaseUrl('https://api.nexmo.com/v1/conversations');

        $this->conversationsClient = new ConversationClient($this->api);
    }

    public function testHasSetupClientCorrectly(): void
    {
        $this->assertInstanceOf(ConversationClient::class, $this->conversationsClient);
    }

    public function testWillUseCorrectAuth(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );

            return true;
        }))->willReturn($this->getResponse('list-conversations'));

        $this->conversationsClient->listConversations();
        $this->assertTrue(true);
    }

    public function testWillListConversations(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$requestIndex) {
            $this->requestIndex++;
            $this->assertEquals('GET', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();

            if ($requestIndex == 1) {
                $this->assertEquals('https://api.nexmo.com/v1/conversations', $uriString);
            }

            if ($requestIndex == 2) {
                $this->assertEquals('https://api.nexmo.com/v1/conversations?order=desc&page_size=10&cursor=7EjDNQrAcipmOnc0HCzpQRkhBULzY44ljGUX4lXKyUIVfiZay5pv9wg=');
            }

            return true;
        }))->willReturn($this->getResponse('list-conversations'));

        $response = $this->conversationsClient->listConversations();
        $this->assertInstanceOf(IterableAPICollection::class, $response);

        $conversations = [];

        foreach ($response as $conversation) {
            $conversations[] = $conversation;
        }

        $this->assertInstanceOf(Conversation::class, $conversations[0]);

        $conversationEntity = $conversations[0];

        $expectedEntityValues = [
            'id' => 'CON-d66d47de-5bcb-4300-94f0-0c9d4b948e9a',
            'name' => 'customer_chat',
            'display_name' => 'Customer Chat',
            'image_url' => 'https://example.com/image.png',
            'timestamp' => [
                'created' => '2019-09-03T18:40:24.324Z',
                'updated' => '2019-09-03T18:40:24.324Z',
                'destroyed' => '2019-09-03T18:40:24.324Z'
            ],
            '_links' => [
                'self' => [
                    'href' => 'https://api.nexmo.com/v1/conversations/CON-d66d47de-5bcb-4300-94f0-0c9d4b948e9a'
                ]
            ]
        ];

        $this->assertEquals($expectedEntityValues, $conversationEntity->toArray());

        $this->requestIndex = 0;
    }

    public function testWillListConversationsByQueryParameters(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) use (&$requestIndex) {
            $this->assertEquals('GET', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();

            $this->assertEquals('https://api.nexmo.com/v1/conversations?order=desc&page_size=10&cursor=7EjDNQrAcipmOnc0HCzpQRkhBULzY44ljGUX4lXKyUIVfiZay5pv9wg=', $uriString);

            return true;
        }))->willReturn($this->getResponse('list-conversations'));

        $filter = new ListConversationFilter();
        $filter->setStartDate('2018-01-01 10:00:00');
        $filter->setEndDate('2018-01-01 12:00:00');
        $filter->setPageSize(5);
        $filter->setOrder('asc');

        $response = $this->conversationsClient->listConversations($filter);
        $this->assertInstanceOf(IterableAPICollection::class, $response);

        $conversations = [];

        foreach ($response as $conversation) {
            $conversations[] = $conversation;
        }

        $this->assertInstanceOf(Conversation::class, $conversations[0]);
    }

    public function testWillCreateConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillRetrieveConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillUpdateConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillDeleteConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillListMembersByConversationId(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillCreateMemberInConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillGetMeAsMemberInConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillGetMemberInConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillUpdateMemberInConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillCreateEventInConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillGetEventsFromConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillGetEventFromConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillDeleteEventFromConversation(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillGetUserConversations(): void
    {
        $this->markTestIncomplete();
    }

    public function testWillListUserSessions(): void
    {
        $this->markTestIncomplete();
    }

    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/Fixtures/Responses/' . $identifier . '.json', 'rb'), $status);
    }
}
