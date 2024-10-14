<?php

namespace VonageTest\Meetings;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\KeypairHandler;
use Vonage\Client\Exception\Conflict;
use Vonage\Client\Exception\NotFound;
use Vonage\Client\Exception\Validation;
use Vonage\Meetings\Application;
use Vonage\Meetings\ApplicationTheme;
use Vonage\Meetings\Client as MeetingsClient;
use Vonage\Meetings\DialInNumber;
use Vonage\Meetings\ExceptionErrorHandler;
use Vonage\Meetings\Recording;
use Vonage\Meetings\Room;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\Traits\Psr7AssertionTrait;

class ClientTest extends TestCase
{
    use ProphecyTrait;
    use Psr7AssertionTrait;
    use HTTPTestTrait;

    private APIResource $api;

    private MeetingsClient $meetingsClient;

    private ObjectProphecy|Client $vonageClient;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/Fixtures/Responses/';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://api-eu.vonage.com/v1/meetings');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Keypair(
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def'
            ))
        );

        $this->api = (new APIResource())
            ->setIsHAL(true)
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
            ->setClient($this->vonageClient->reveal())
            ->setAuthHandlers(new KeypairHandler())
            ->setBaseUrl('https://api-eu.vonage.com/v1/meetings');
        $this->meetingsClient = @new MeetingsClient($this->api);
    }

    public function testBaseUrlIsSet(): void
    {
        $this->assertEquals(
            'https://api-eu.vonage.com/v1/meetings',
            $this->meetingsClient->getAPIResource()->getBaseUrl()
        );
    }

    public function testWillGetAvailableRooms(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/rooms?page_size=20', $uriString);

            return true;
        }))->willReturn($this->getResponse('get-rooms-success'));

        $response = @$this->meetingsClient->getAllListedRooms();
        $this->assertCount(2, $response);

        foreach ($response as $room) {
            $this->assertInstanceOf(Room::class, $room);
        }
    }

    public function testWillGetAvailableRoomsWithFilter(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/rooms?start_id=999&end_id=234&page_size=20', $uriString);

            return true;
        }))->willReturn($this->getResponse('get-rooms-success'));

        $response = @$this->meetingsClient->getAllListedRooms('999', '234');
        $this->assertCount(2, $response);

        foreach ($response as $room) {
            $this->assertInstanceOf(Room::class, $room);
        }
    }

    public function testWillCreateRoom(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/rooms', $uriString);

            $this->assertRequestJsonBodyContains('display_name', 'test-room', $request);
            return true;
        }))->willReturn($this->getResponse('create-room-success', 201));

        $room = new Room();
        $room->fromArray(['display_name' => 'test-room']);

        $response = @$this->meetingsClient->createRoom($room);
        $this->assertInstanceOf(Room::class, $response);

        $this->assertEquals('test-room', $response->display_name);
        $this->assertEquals('instant', $response->type);
    }

    public function testWillCreateLongTermRoom(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/rooms', $uriString);

            $this->assertRequestJsonBodyContains('display_name', 'test-room', $request);
            return true;
        }))->willReturn($this->getResponse('create-long-term-room-success', 201));

        $room = new Room();
        $room->fromArray([
            'display_name' => 'test-room',
            'type' => 'long_term',
            'expires_at' => '2023-01-30T00:47:04+0000'
        ]);

        $response = @$this->meetingsClient->createRoom($room);
        $this->assertInstanceOf(Room::class, $response);

        $this->assertEquals('test-room', $response->display_name);
        $this->assertEquals('long_term', $response->type);
    }

    public function testClientWillHandleUnauthorizedRequests(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/rooms', $uriString);

            $this->assertRequestJsonBodyContains('display_name', 'something', $request);
            return true;
        }))->willReturn($this->getResponse('empty', 403));

        $this->expectException(Client\Exception\Credentials::class);
        $this->expectExceptionMessage('You are not authorised to perform this request');

        $room = new Room();
        $room->fromArray(['display_name' => 'something']);

        $response = @$this->meetingsClient->createRoom($room);
    }

    public function testClientWillHandleNotFoundResponse(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v1/meetings/rooms/224d6219-dc05-4c09-9d42-96adce7fcb67',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('empty', 404));
        $this->expectException(NotFound::class);
        $this->expectExceptionMessage('No resource found');
        $response = @$this->meetingsClient->getRoom('224d6219-dc05-4c09-9d42-96adce7fcb67');
    }

    public function testClientWillHandleValidationError(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/rooms', $uriString);

            return true;
        }))->willReturn($this->getResponse('empty', 400));

        $this->expectException(Validation::class);
        $this->expectExceptionMessage('The request data was invalid');

        $room = new Room();
        $room->fromArray(['display_name' => 'test-room']);

        $response = @$this->meetingsClient->createRoom($room);
    }

    public function testWillGetRoomDetails(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v1/meetings/rooms/224d6219-dc05-4c09-9d42-96adce7fcb67',
                $uriString
            );
            return true;
        }))->willReturn($this->getResponse('get-room-success'));

        $response = @$this->meetingsClient->getRoom('224d6219-dc05-4c09-9d42-96adce7fcb67');
        $this->assertInstanceOf(Room::class, $response);
        $this->assertEquals('224d6219-dc05-4c09-9d42-96adce7fcb67', $response->id);
    }

    public function testWillUpdateExistingRoom(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('PATCH', $request->getMethod());
            $this->assertRequestJsonBodyContains('microphone_state', 'custom', $request, true);
            $this->assertRequestJsonBodyContains('rooms_callback_url', 'https://my-callback-url', $request, true);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v1/meetings/rooms/e857c5ce-cdee-4971-ab20-208a98263282',
                $uriString
            );

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

        $response = @$this->meetingsClient->updateRoom('e857c5ce-cdee-4971-ab20-208a98263282', $payload);
        $this->assertInstanceOf(Room::class, $response);
        $this->assertEquals('custom', $response->initial_join_options['microphone_state']);
        $this->assertEquals('https://my-callback-url', $response->callback_urls['rooms_callback_url']);
    }

    public function testWillGetRecording(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v1/meetings/recordings/2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('get-recording-success'));

        $response = @$this->meetingsClient->getRecording('2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885');
        $this->assertInstanceOf(Recording::class, $response);
        $this->assertEquals('2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885', $response->id);
    }

    public function testWillDeleteRecording(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('DELETE', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v1/meetings/recordings/2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $response = @$this->meetingsClient->deleteRecording('2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885');
        $this->assertTrue($response);
    }

    public function testWillGetRecordingsFromSession(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v1/meetings/sessions/2_MX40NjMwODczMn5-MTU3NTgyODEwNzQ2MH5OZDJrVmdBRUNDbG5MUzNqNXgya20yQ1Z-fg/recordings',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('get-recordings-success'));

        $response = @$this->meetingsClient->getRecordingsFromSession('2_MX40NjMwODczMn5-MTU3NTgyODEwNzQ2MH5OZDJrVmdBRUNDbG5MUzNqNXgya20yQ1Z-fg');

        foreach ($response as $recording) {
            $this->assertInstanceOf(Recording::class, $recording);
        }
    }

    public function testWillGetMeetingDialNumbers(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v1/meetings/dial-in-numbers',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('get-dialin-success'));

        $response = @$this->meetingsClient->getDialInNumbers();

        foreach ($response as $dialInNumber) {
            $this->assertInstanceOf(DialInNumber::class, $dialInNumber);
        }
    }

    public function testWillGetApplicationThemes(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api-eu.vonage.com/v1/meetings/themes',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('get-application-themes-success'));

        $response = @$this->meetingsClient->getApplicationThemes();

        foreach ($response as $applicationThemes) {
            $this->assertInstanceOf(ApplicationTheme::class, $applicationThemes);
        }
    }

    public function testWillCreateTheme(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes', $uriString);

            $this->assertRequestJsonBodyContains('theme_name', 'My-Theme', $request);

            return true;
        }))->willReturn($this->getResponse('create-theme-success', 201));

        $response = @$this->meetingsClient->createApplicationTheme('My-Theme');
        $this->assertInstanceOf(ApplicationTheme::class, $response);

        $this->assertEquals('My-Theme', $response->theme_name);
    }

    public function testWillHandleConflictErrorOnThemeCreation(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('POST', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes', $uriString);

            $this->assertRequestJsonBodyContains('theme_name', 'My-Theme', $request);

            return true;
        }))->willReturn($this->getResponse('empty', 409));

        $this->expectException(Conflict::class);
        $this->expectExceptionMessage('Entity conflict');
        $response = @$this->meetingsClient->createApplicationTheme('My-Theme');
    }

    public function testWillGetThemeById(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/afb5b1f2-fe83-4b14-83ff-f23f5630c160', $uriString);

            return true;
        }))->willReturn($this->getResponse('get-theme-success'));

        $response = @$this->meetingsClient->getThemeById('afb5b1f2-fe83-4b14-83ff-f23f5630c160');
        $this->assertInstanceOf(ApplicationTheme::class, $response);
        $this->assertEquals('afb5b1f2-fe83-4b14-83ff-f23f5630c160', $response->theme_id);
    }

    public function testWillDeleteTheme(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('DELETE', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885', $uriString);

            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $response = @$this->meetingsClient->deleteTheme('2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885');
        $this->assertTrue($response);
    }

    public function testWillForceDeleteTheme(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('DELETE', $request->getMethod());

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885?force=true', $uriString);

            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $response = @$this->meetingsClient->deleteTheme('2dbd1cf7-afbb-45d8-9fb6-9e95ce2f8885', true);
        $this->assertTrue($response);
    }

    public function testWillUpdateThemeById(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('PATCH', $request->getMethod());
            $this->assertRequestJsonBodyContains('theme_name', 'Updated Theme', $request, true);
            $this->assertRequestJsonBodyContains('brand_text', 'Updated Branding', $request, true);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/afb5b1f2-fe83-4b14-83ff-f23f5630c160', $uriString);

            return true;
        }))->willReturn($this->getResponse('update-theme-success'));

        $payload = [
            'update_details' => [
                'theme_name' => 'Updated Theme',
                'brand_text' => 'Updated Branding'
            ]
        ];

        $response = @$this->meetingsClient->updateTheme('afb5b1f2-fe83-4b14-83ff-f23f5630c160', $payload);
        $this->assertInstanceOf(ApplicationTheme::class, $response);
        $this->assertEquals('Updated Theme', $response->theme_name);
        $this->assertEquals('Updated Branding', $response->brand_text);
    }

    /**
     * @dataProvider logoTypeDataProvider
     */
    public function testWillExtractCorrectImageKey($logoType, $validCall): void
    {
        if (!$validCall) {
            $this->expectException(NotFound::class);
        }

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            if ($request->getMethod() === 'GET') {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/logos-upload-urls', $uriString);

                return true;
            }
        }))->willReturn($this->getResponse('get-upload-urls-success'));

        $uploadUrls = @$this->meetingsClient->getUploadUrls();

        $entity = @$this->meetingsClient->returnCorrectUrlEntityFromType($uploadUrls, $logoType);

        if ($validCall) {
            $this->assertEquals($logoType, $entity->fields['logoType']);
        }
    }

    public function testWillUploadImageToAws(): void
    {
        // Two calls to predict, one is the getUploadUrls, the other finalize
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            if ($request->getMethod() === 'GET') {
                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/logos-upload-urls', $uriString);

                return true;
            }

            if ($request->getMethod() === 'PUT') {
                $this->assertRequestJsonBodyContains('keys', ['auto-expiring-temp/logos/white/ca63a155-d5f0-4131-9903-c59907e53df0'], $request);

                $uri = $request->getUri();
                $uriString = $uri->__toString();
                $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/afb5b1f2-fe83-4b14-83ff-f23f5630c160/finalizeLogos', $uriString);

                return true;
            }
        }))->willReturn($this->getResponse('get-upload-urls-success'), $this->getResponse('empty'));

        // Testing the AWS upload request
        $httpClient = (new Prophet())->prophesize();
        $httpClient->willImplement(ClientInterface::class);
        $httpClient->sendRequest(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('PUT', $request->getMethod());

            $headers = $request->getHeaders();
            $this->assertEquals('multipart/form-data', $headers['Content-Type'][0]);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://s3.amazonaws.com/php-sdk/white', $uriString);

            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $this->vonageClient->getHttpClient()->willReturn($httpClient);

        $file = __DIR__ . '/Fixtures/vonage.png';
        @$this->meetingsClient->uploadImage('afb5b1f2-fe83-4b14-83ff-f23f5630c160', 'white', $file);
    }

    public function testCanGetUploadUrlsForThemeLogo(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/logos-upload-urls', $uriString);

            return true;
        }))->willReturn($this->getResponse('get-upload-urls-success'));

        $response = @$this->meetingsClient->getUploadUrls();
        $this->assertEquals('auto-expiring-temp/logos/white/ca63a155-d5f0-4131-9903-c59907e53df0', $response[0]->fields['key']);
    }

    public function testWillGetRoomsAssociatedWithTheme(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/323867d7-8c4b-4dce-8c11-48f14425d888/rooms?page_size=20', $uriString);

            return true;
        }))->willReturn($this->getResponse('get-rooms-by-theme-id-success'));

        $response = @$this->meetingsClient->getRoomsByThemeId('323867d7-8c4b-4dce-8c11-48f14425d888');

        foreach ($response as $room) {
            $this->assertInstanceOf(Room::class, $room);
        }
    }

    public function testWillGetRoomsAssociatedWithThemeUsingFilter(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestMethod('GET', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/themes/323867d7-8c4b-4dce-8c11-48f14425d888/rooms?start_id=245&end_id=765&page_size=20', $uriString);

            return true;
        }))->willReturn($this->getResponse('get-rooms-by-theme-id-success'));

        $response = @$this->meetingsClient->getRoomsByThemeId('323867d7-8c4b-4dce-8c11-48f14425d888', startId: '245', endId: '765');

        foreach ($response as $room) {
            $this->assertInstanceOf(Room::class, $room);
        }
    }

    public function testWillUpdateExistingApplication(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('PATCH', $request->getMethod());
            $this->assertRequestJsonBodyContains('default_theme_id', '323867d7-8c4b-4dce-8c11-48f14425d888', $request);

            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals('https://api-eu.vonage.com/v1/meetings/applications', $uriString);

            return true;
        }))->willReturn($this->getResponse('update-application-success'));

        $payload = [
            'default_theme_id' => '323867d7-8c4b-4dce-8c11-48f14425d888',
        ];

        $response = @$this->meetingsClient->updateApplication($payload);
        $this->assertInstanceOf(Application::class, $response);
        $this->assertEquals('f4d5a07b-260c-4458-b16c-e5a68553bc85', $response->application_id);
        $this->assertEquals('323867d7-8c4b-4dce-8c11-48f14425d888', $response->default_theme_id);
    }

    public function logoTypeDataProvider(): array
    {
        return [
            ['white', true],
            ['colored', true],
            ['favicon', true],
            ['png', false]
        ];
    }
}
