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
use Vonage\Meetings\Recording;
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
            $this->assertRequestJsonBodyContains('display_name', 'test-room', $request);
            //TODO make the path correct
            return true;
        }))->willReturn($this->getResponse('create-room-success', 201));

        $response = $this->meetingsClient->createRoom('test-room');
        $this->assertInstanceOf(Room::class, $response);

        $this->assertEquals('test-room', $response->display_name);
    }

    public function testWillGetRoomDetails(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('GET', $request->getMethod());
            //TODO make the path correct
            return true;
        }))->willReturn($this->getResponse('get-room-success'));

        $response = $this->meetingsClient->getRoom('224d6219-dc05-4c09-9d42-96adce7fcb67');
        $this->assertInstanceOf(Room::class, $response);
        $this->assertEquals('224d6219-dc05-4c09-9d42-96adce7fcb67', $response->id);
    }

    public function testWillUpdateExistingRoom(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('PATCH', $request->getMethod());
            $this->assertRequestJsonBodyContains('microphone_state', 'custom', $request, true);
            $this->assertRequestJsonBodyContains('rooms_callback_url', 'https://my-callback-url', $request, true);
            //TODO make the path correct
            return true;
        }))->willReturn($this->getResponse('update-details-success'));

        $payload = [
            'update_details' => [
                'initial_join_options' => [
                    'microphone_state' => 'custom'
                ],
                'callback_urls' => [
                    'rooms_callback_url' => 'https://my-callback-url'
                ],
            ]
        ];

        $response = $this->meetingsClient->updateRoom('e857c5ce-cdee-4971-ab20-208a98263282', $payload);
        $this->assertInstanceOf(Room::class, $response);
        $this->assertEquals('custom', $response->initial_join_options['microphone_state']);
        $this->assertEquals('https://my-callback-url', $response->callback_urls['rooms_callback_url']);
    }

    public function testWillGetRecording(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('GET', $request->getMethod());
            //TODO make the path correct
            return true;
        }))->willReturn($this->getResponse('get-recording-success'));

        $response = $this->meetingsClient->getRecording('2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885');
        $this->assertInstanceOf(Recording::class, $response);
        $this->assertEquals('2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885', $response->id);
    }

    public function testWillDeleteRecording(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('DELETE', $request->getMethod());
            //TODO make the path correct
            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $response = $this->meetingsClient->deleteRecording('2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885');
        $this->assertTrue($response);
    }

    public function testWillGetRecordingsFromSession(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('DELETE', $request->getMethod());
            //TODO make the path correct
            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $response = $this->meetingsClient->deleteRecording('2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885');
        $this->assertTrue($response);
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
