<?php

namespace VonageTest\Meetings;

use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\KeypairHandler;
use Vonage\Meetings\Client as MeetingsClient;
use PHPUnit\Framework\TestCase;
use Vonage\Meetings\Room;
use VonageTest\Psr7AssertionTrait;

class ClientTest extends TestCase
{
    use ProphecyTrait;
    use Psr7AssertionTrait;

    private APIResource $api;

    private MeetingsClient $meetingsClient;

    private ObjectProphecy|Client $vonageClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://api-eu.vonage.com/beta/meetings');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Keypair(
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def'
            ))
        );

        $this->api = (new APIResource())
            ->setIsHAL(true)
            ->setClient($this->vonageClient->reveal())
            ->setAuthHandler(new KeypairHandler())
            ->setBaseUrl('https://api-eu.vonage.com/beta/meetings');
        $this->meetingsClient = new MeetingsClient($this->api);
    }

    public function testBaseUrlIsSet(): void
    {
        $this->assertEquals(
            'https://api-eu.vonage.com/beta/meetings',
            $this->meetingsClient->getAPIResource()->getBaseUrl()
        );
    }

    public function testWillGetAvailableRooms(): void
    {
        $this->markTestSkipped('incomplete');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('GET', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('get-rooms-success'));

        $response = $this->meetingsClient->getAllAvailableRooms();
        $this->assertCount(1, $response);

        foreach ($response as $room) {
            $this->assertInstanceOf(Room::class, $room);
        }
    }

    public function testWillCreateRoom(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->willReturn($this->getResponse('create-room-success'));

        $response = $this->meetingsClient->createRoom('test-room');
        $this->assertInstanceOf(Room::class, $response);

        $this->assertEquals('test-room', $response->display_name);
    }

    public function testWillGetRoomDetails(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillUpdateExistingRoom(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillGetRecording(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillDeleteRecording(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillGetRecordingsFromSession(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillGetMeetingDialNumbers(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillGetApplicationThemes(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillCreateTheme(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillGetThemeById(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillDeleteTheme(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillUpdateThemeById(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillChangeLogo(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testCanGetUploadUrlsForThemeLogo(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillGetRoomsAssociatedWithTheme(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    public function testWillUpdateExistingApplication(): void
    {
        $this->markTestIncomplete('Not written yet');
    }

    /**
     * This method gets the fixtures and wraps them in a Response object to mock the API
     */
    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/Fixtures/Responses/' . $identifier . '.json', 'rb'), $status);
    }
}
