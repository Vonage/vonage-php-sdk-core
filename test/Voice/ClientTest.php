<?php

declare(strict_types=1);

namespace VonageTest\Voice;

use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Entity\IterableAPICollection;
use Vonage\Voice\CallAction;
use Vonage\Voice\Client as VoiceClient;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\Filter\VoiceFilter;
use Vonage\Voice\NCCO\Action\Talk;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\OutboundCall;
use Vonage\Voice\VoiceObjects\AdvancedMachineDetection;
use Vonage\Voice\Webhook;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\VonageTestCase;

use function fopen;
use function json_decode;
use function json_encode;

class ClientTest extends VonageTestCase
{
    use HTTPTestTrait;

    protected $api;

    protected $vonageClient;

    /**
     * @var VoiceClient
     */
    protected $voiceClient;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Keypair(
                file_get_contents(__DIR__ . '/../Client/Credentials/test.key'),
                'def'
            ))
        );

        /** @noinspection PhpParamsInspection */
        $this->api = $this->prophesize(APIResource::class);
        $this->api->getBaseUri()->willReturn('/v1/calls');
        $this->api->getCollectionName()->willReturn('calls');
        $this->api->isHAL()->willReturn(true);
        $this->api->getAuthHandlers()->willReturn([new Client\Credentials\Handler\KeypairHandler()]);

        $this->voiceClient = new VoiceClient($this->api->reveal());
    }

    /**
     * @throws Client\Exception\Exception
     * @throws ClientExceptionInterface
     */
    public function testCanCreateOutboundCall(): void
    {
        $payload = [
            'to' => [
                [
                    'type' => 'phone',
                    'number' => '15555555555'
                ]
            ],
            'from' => [
                'type' => 'phone',
                'number' => '16666666666'
            ],
            'answer_url' => ['http://domain.test/answer'],
            'answer_method' => 'POST',
            'event_url' => ['http://domain.test/event'],
            'event_method' => 'POST',
            'machine_detection' => 'hangup',
            'length_timer' => '7200',
            'ringing_timer' => '60'
        ];

        $this->api->create(Argument::that(function (array $requestPayload) use ($payload) {
            $this->assertSame(json_encode($payload), json_encode($requestPayload));

            return true;
        }))->willReturn([
            'uuid' => 'e46fd8bd-504d-4044-9600-26dd18b41111',
            'status' => 'started',
            'direction' => 'outbound',
            'conversation_uuid' => '2541d01c-253e-48be-a8e0-da4bbe4c3722'
        ]);

        $outboundCall = (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setEventWebhook(new Webhook('http://domain.test/event'))
            ->setAnswerWebhook(new Webhook('http://domain.test/answer'))
            ->setRingingTimer((int)$payload['ringing_timer'])
            ->setLengthTimer((int)$payload['length_timer'])
            ->setMachineDetection(OutboundCall::MACHINE_HANGUP);
        $callData = $this->voiceClient->createOutboundCall($outboundCall);

        $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $callData->getUuid());
        $this->assertEquals('started', $callData->getStatus());
        $this->assertEquals('outbound', $callData->getDirection());
        $this->assertEquals('2541d01c-253e-48be-a8e0-da4bbe4c3722', $callData->getConversationUuid());
    }

    public function testAdvancedMachineDetectionRenders(): void
    {
        $advancedMachineDetection = new AdvancedMachineDetection(
            AdvancedMachineDetection::MACHINE_BEHAVIOUR_CONTINUE,
            50,
            AdvancedMachineDetection::MACHINE_MODE_DETECT_BEEP
        );

        $payload = [
            'to' => [
                [
                    'type' => 'phone',
                    'number' => '15555555555'
                ]
            ],
            'from' => [
                'type' => 'phone',
                'number' => '16666666666'
            ],
            'answer_url' => ['http://domain.test/answer'],
            'answer_method' => 'POST',
            'event_url' => ['http://domain.test/event'],
            'event_method' => 'POST',
            'length_timer' => '7200',
            'ringing_timer' => '60',
            'advanced_machine_detection' => $advancedMachineDetection->toArray()
        ];

        $this->api->create(Argument::that(function (array $requestPayload) use ($payload) {
            $this->assertSame(json_encode($payload), json_encode($requestPayload));

            return true;
        }))->willReturn([
            'uuid' => 'e46fd8bd-504d-4044-9600-26dd18b41111',
            'status' => 'started',
            'direction' => 'outbound',
            'conversation_uuid' => '2541d01c-253e-48be-a8e0-da4bbe4c3722'
        ]);

        $outboundCall = (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setEventWebhook(new Webhook('http://domain.test/event'))
            ->setAnswerWebhook(new Webhook('http://domain.test/answer'))
            ->setAdvancedMachineDetection($advancedMachineDetection);

        $callData = $this->voiceClient->createOutboundCall($outboundCall);
    }

    public function testCanCreateOutboundCallWithRandomFromNumber(): void
    {
        $payload = [
            'to' => [
                [
                    'type' => 'phone',
                    'number' => '15555555555'
                ]
            ],
            'random_from_number' => true,
            'answer_url' => ['http://domain.test/answer'],
            'answer_method' => 'POST',
            'event_url' => ['http://domain.test/event'],
            'event_method' => 'POST',
            'machine_detection' => 'hangup',
            'length_timer' => '7200',
            'ringing_timer' => '60',
        ];

        $this->api->create(Argument::that(function (array $requestPayload) use ($payload) {
            $this->assertSame(json_encode($payload), json_encode($requestPayload));

            return true;
        }))->willReturn([
            'uuid' => 'e46fd8bd-504d-4044-9600-26dd18b41111',
            'status' => 'started',
            'direction' => 'outbound',
            'conversation_uuid' => '2541d01c-253e-48be-a8e0-da4bbe4c3722'
        ]);

        $outboundCall = (new OutboundCall(new Phone('15555555555')))
            ->setEventWebhook(new Webhook('http://domain.test/event'))
            ->setAnswerWebhook(new Webhook('http://domain.test/answer'))
            ->setRingingTimer((int)$payload['ringing_timer'])
            ->setLengthTimer((int)$payload['length_timer'])
            ->setMachineDetection(OutboundCall::MACHINE_HANGUP);
        $callData = $this->voiceClient->createOutboundCall($outboundCall);

        $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $callData->getUuid());
        $this->assertEquals('started', $callData->getStatus());
        $this->assertEquals('outbound', $callData->getDirection());
        $this->assertEquals('2541d01c-253e-48be-a8e0-da4bbe4c3722', $callData->getConversationUuid());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanCreateOutboundCallWithNCCO(): void
    {
        $payload = [
            'to' => [
                [
                    'type' => 'phone',
                    'number' => '15555555555'
                ]
            ],
            'from' => [
                'type' => 'phone',
                'number' => '16666666666'
            ],
            'event_url' => ['http://domain.test/event'],
            'event_method' => 'POST',
            'ncco' => [
                [
                    'action' => 'talk',
                    'text' => 'Thank you for trying Vonage',
                    'bargeIn' => 'false',
                    'level' => '0',
                    'loop' => '1',
                    'premium' => 'false'
                ]
            ],
            'length_timer' => '7200',
            'ringing_timer' => '60'
        ];

        $this->api->create(Argument::that(function (array $requestPayload) use ($payload) {
            $this->assertSame(json_encode($payload), json_encode($requestPayload));

            return true;
        }))->willReturn([
            'uuid' => 'e46fd8bd-504d-4044-9600-26dd18b41111',
            'status' => 'started',
            'direction' => 'outbound',
            'conversation_uuid' => '2541d01c-253e-48be-a8e0-da4bbe4c3722'
        ]);

        $outboundCall = (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setEventWebhook(new Webhook('http://domain.test/event'))
            ->setNCCO((new NCCO())->addAction(new Talk('Thank you for trying Vonage')))
            ->setLengthTimer(7200)
            ->setRingingTimer(60);
        $callData = $this->voiceClient->createOutboundCall($outboundCall);

        $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $callData->getUuid());
        $this->assertEquals('started', $callData->getStatus());
        $this->assertEquals('outbound', $callData->getDirection());
        $this->assertEquals('2541d01c-253e-48be-a8e0-da4bbe4c3722', $callData->getConversationUuid());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanHandleErrorWhileCreatingOutboundCall(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Bad Request');

        $this->api->create(Argument::that(function (array $requestPayload) {
            $this->assertSame(json_encode([
                'to' => [new Phone('15555555555')],
                'from' => new Phone('16666666666'),
                'length_timer' => '7200',
                'ringing_timer' => '60'
            ]), json_encode($requestPayload));

            return true;
        }))->willThrow(new RequestException('Bad Request', 400));

        $outboundCall = new OutboundCall(new Phone('15555555555'), new Phone('16666666666'));
        $this->voiceClient->createOutboundCall($outboundCall);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCreateOutboundCallErrorUnknownFormat(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage("Unexpected error");

        $this->api->create(Argument::that(
            fn () => true
        ))->willThrow(new RequestException('Unexpected error', 400));

        $outboundCall = new OutboundCall(new Phone('15555555555'), new Phone('16666666666'));
        $this->voiceClient->createOutboundCall($outboundCall);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanRetrieveCallInformation(): void
    {
        $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';
        $response = $this->getResponse('call', 200);
        $data = json_decode($response->getBody()->getContents(), true);
        $response->getBody()->rewind();

        $this->api->get(Argument::that(function (string $callId) use ($id) {
            $this->assertSame($id, $callId);

            return true;
        }))->willReturn($data);

        $call = $this->voiceClient->get($id);

        $this->assertEquals($id, $call->getUuid());
        $this->assertEquals('447700900000', $call->getTo()->getId());
        $this->assertEquals('447700900001', $call->getFrom()->getId());
        $this->assertEquals('started', $call->getStatus());
        $this->assertEquals('outbound', $call->getDirection());
        $this->assertEquals('0.39', $call->getRate());
        $this->assertEquals('23.40', $call->getPrice());
        $this->assertEquals('60', $call->getDuration());
        $this->assertEquals('2020-01-01 12:00:00', $call->getStartTime()->format('Y-m-d H:i:s'));
        $this->assertEquals('2020-01-01 12:00:00', $call->getEndTime()->format('Y-m-d H:i:s'));
        $this->assertEquals('65512', $call->getNetwork());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanModifyACallLeg(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'earmuff'];

        $this->api->update(
            Argument::that(function (string $callId) use ($id) {
                $this->assertSame($id, $callId);

                return true;
            }),
            Argument::that(function (array $requestPayload) use ($payload) {
                $this->assertSame($payload, $requestPayload);

                return true;
            })
        )->willReturn([]);

        $this->voiceClient->modifyCall($id, CallAction::EARMUFF);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanEarmuffCall(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'earmuff'];

        $this->api->update(
            Argument::that(function (string $callId) use ($id): bool {
                $this->assertSame($id, $callId);

                return true;
            }),
            Argument::that(function (array $requestPayload) use ($payload): bool {
                $this->assertSame($payload, $requestPayload);

                return true;
            })
        )->willReturn([]);

        $this->voiceClient->earmuffCall($id);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanUnearmuffCall(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'unearmuff'];

        $this->api->update(
            Argument::that(function (string $callId) use ($id): bool {
                $this->assertSame($id, $callId);

                return true;
            }),
            Argument::that(function (array $requestPayload) use ($payload): bool {
                $this->assertSame($payload, $requestPayload);

                return true;
            })
        )->willReturn([]);

        $this->voiceClient->unearmuffCall($id);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanMuteCall(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'mute'];

        $this->api->update(
            Argument::that(function (string $callId) use ($id): bool {
                $this->assertSame($id, $callId);

                return true;
            }),
            Argument::that(function (array $requestPayload) use ($payload): bool {
                $this->assertSame($payload, $requestPayload);

                return true;
            })
        )->willReturn([]);

        $this->voiceClient->muteCall($id);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanUnmuteCall(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'unmute'];

        $this->api->update(
            Argument::that(function (string $callId) use ($id): bool {
                $this->assertSame($id, $callId);

                return true;
            }),
            Argument::that(function (array $requestPayload) use ($payload): bool {
                $this->assertSame($payload, $requestPayload);

                return true;
            })
        )->willReturn([]);

        $this->voiceClient->unmuteCall($id);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanHangupCall(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'hangup'];

        $this->api->update(
            Argument::that(function (string $callId) use ($id): bool {
                $this->assertSame($id, $callId);

                return true;
            }),
            Argument::that(function (array $requestPayload) use ($payload): bool {
                $this->assertSame($payload, $requestPayload);

                return true;
            })
        )->willReturn([]);

        $this->voiceClient->hangupCall($id);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanTransferACallLegWithNCCO(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'ncco' => [
                    [
                        'action' => 'talk',
                        'text' => 'Thank you for trying Vonage',
                        'bargeIn' => 'false',
                        'level' => '0',
                        'loop' => '1',
                        'premium' => 'false'
                    ]
                ]
            ],
        ];

        $this->api->update(
            Argument::that(function (string $callId) use ($id): bool {
                $this->assertSame($id, $callId);

                return true;
            }),
            Argument::that(function (array $requestPayload) use ($payload): bool {
                $this->assertSame($payload, $requestPayload);

                return true;
            })
        )->willReturn([]);

        $ncco = (new NCCO())
            ->addAction(new Talk('Thank you for trying Vonage'));

        $this->voiceClient->transferCallWithNCCO($id, $ncco);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanTransferACallLegWithURL(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => ['https://test.domain/transfer.json'],
            ],
        ];

        $this->api->update(
            Argument::that(function (string $callId) use ($id): bool {
                $this->assertSame($id, $callId);

                return true;
            }),
            Argument::that(function (array $requestPayload) use ($payload): bool {
                $this->assertSame($payload, $requestPayload);

                return true;
            })
        )->willReturn([]);

        $this->voiceClient->transferCallWithUrl($id, 'https://test.domain/transfer.json');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testcanStreamAudioIntoCall(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $url = 'http://domain.test/music.mp3';
        $payload = [
            'stream_url' => [$url],
            'loop' => '1',
            'level' => '0',
        ];

        $this->api->update(
            Argument::that(fn (string $callId): bool => $callId === $id . '/stream'),
            Argument::that(fn (array $requestPayload): bool => $requestPayload === $payload)
        )->willReturn([
            'message' => 'Stream started',
            'uuid' => 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb'
        ]);

        $response = $this->voiceClient->streamAudio($id, $url);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('Stream started', $response['message']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanStopStreamingAudioIntoCall(): void
    {
        $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';

        $this->api->delete(Argument::that(fn (string $callId): bool => $callId === $id . '/stream'))
            ->willReturn([
                'message' => 'Stream stopped',
                'uuid' => '63f61863-4a51-4f6b-86e1-46edebcf9356'
            ]);

        $response = $this->voiceClient->stopStreamAudio($id);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('Stream stopped', $response['message']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanPlayTTSIntoCall(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = [
            'text' => 'This is sample text',
            'bargeIn' => 'false',
            'level' => '0',
            'loop' => '1',
            'premium' => 'false'
        ];

        $this->api->update(
            Argument::that(fn (string $callId): bool => $callId === $id . '/talk'),
            Argument::that(fn (array $requestPayload): bool => $requestPayload === $payload)
        )->willReturn([
            'message' => 'Talk started',
            'uuid' => 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb'
        ]);

        $action = new Talk('This is sample text');
        $response = $this->voiceClient->playTTS($id, $action);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('Talk started', $response['message']);
    }

    public function testCanSubscribeToDtmfEvents(): void
    {
        $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';

        $payload = [
            'https://example.com/events'
        ];

        $this->api->update(
            Argument::that(function (string $callId) use ($id): bool {
                $this->assertSame($id . '/input/dtmf', $callId);

                return true;
            }),
            Argument::that(function (array $requestPayload): bool {
                $this->assertSame(['eventUrl' => ['https://example.com/events']], $requestPayload);

                return true;
            })
        )->willReturn([]);

        $this->voiceClient->subscribeToDtmfEventsById($id, $payload);
    }

    public function testCanUnsubscribeToDtmfEvents(): void
    {
        $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';

        $this->api->delete(Argument::that(function (string $callId) use ($id): bool {
            $this->assertSame($id . '/input/dtmf', $callId);

            return true;
        }))
            ->willReturn([]);

        $this->voiceClient->unsubscribeToDtmfEventsById($id);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanStopTTSInCall(): void
    {
        $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';

        $this->api->delete(Argument::that(fn (string $callId): bool => $callId === $id . '/talk'))
            ->willReturn([
                'message' => 'Talk stopped',
                'uuid' => '63f61863-4a51-4f6b-86e1-46edebcf9356'
            ]);

        $response = $this->voiceClient->stopTTS($id);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('Talk stopped', $response['message']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     */
    public function testCanPlayDTMFIntoCall(): void
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['digits' => '1492'];

        $this->api->update(
            Argument::that(fn (string $callId): bool => $callId === $id . '/dtmf'),
            Argument::that(fn (array $requestPayload): bool => $requestPayload === $payload)
        )->willReturn([
            'message' => 'DTMF sent',
            'uuid' => 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb'
        ]);

        $response = $this->voiceClient->playDTMF($id, $payload['digits']);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('DTMF sent', $response['message']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Server
     * @throws RequestException
     */
    public function testCanSearchCalls(): void
    {
        $response = $this->getResponse('search');
        $data = json_decode($response->getBody()->getContents(), true);
        $response->getBody()->rewind();
        $collection = new IterableAPICollection();
        $collection->setPageData($data);

        $this->api->search(Argument::that(function (VoiceFilter $filter) {
            $query = $filter->getQuery();
            $this->assertSame(10, $query['page_size']);
            $this->assertSame(0, $query['record_index']);
            $this->assertSame('asc', $query['order']);
            $this->assertSame(VoiceFilter::STATUS_STARTED, $query['status']);

            return true;
        }))->willReturn($collection);

        $filter = new VoiceFilter();
        $filter->setStatus(VoiceFilter::STATUS_STARTED);
        $response = $this->voiceClient->search($filter);

        $this->assertCount(1, $response);

        $call = $response->current();

        $this->assertEquals($data['_embedded']['calls'][0]['uuid'], $call->getUuid());
    }

    public function testCanDownloadRecording(): void
    {
        $fixturePath = __DIR__ . '/Fixtures/mp3fixture.mp3';
        $url = 'https://api-us.nexmo.com/v1/files/999f999-526d-4013-87fc-c824f7a443b3';
        $stream = $this->getResponseStream($fixturePath)->getBody();

        $this->api->get(
            Argument::that(fn (string $uri): bool => $uri === $url),
            Argument::that(fn (array $query): bool => $query === []),
            Argument::that(fn (array $headers): bool => $headers === []),
            Argument::that(fn (bool $jsonResponse): bool => $jsonResponse === false),
            Argument::that(fn (bool $uriOverride): bool => $uriOverride === true)
        )->willReturn($stream);

        $result = $this->voiceClient->getRecording($url);

        $this->assertStringEqualsFile($fixturePath, $result->getContents());
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponseStream(string $streamPath, int $status = 200): Response
    {
        return new Response(fopen($streamPath, 'rb'), $status);
    }
}
