<?php
namespace VonageTest\Voice;

use Vonage\Client;
use Vonage\Voice\Call;
use Prophecy\Argument;
use Vonage\Voice\NCCO\NCCO;
use Vonage\Voice\CallAction;
use Zend\Diactoros\Response;
use Vonage\Client\APIResource;
use Vonage\Client\Exception\Request;
use Vonage\Voice\Endpoint\Phone;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\NCCO\Action\Talk;
use VonageTest\Psr7AssertionTrait;
use Vonage\Voice\Client as VoiceClient;
use Vonage\Voice\Webhook\Event;
use Vonage\Voice\Filter\VoiceFilter;
use Vonage\Voice\OutboundCall;
use Vonage\Voice\Webhook;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;
    
    /**
     * @var APIResource
     */
    protected $api;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $vonageClient;

    /**
     * @var VoiceClient
     */
    protected $voiceClient;
    
    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->api = new APIResource();
        $this->api
            ->setBaseUri('/v1/calls')
            ->setCollectionName('calls')
            ->setClient($this->vonageClient->reveal())
        ;

        $this->voiceClient = new VoiceClient($this->api);
    }

    public function testHasAPI()
    {
        $this->assertTrue($this->voiceClient->getAPIResource() instanceof APIResource);
    }

    public function testCanCreateOutboundCall()
    {
        $payload = [
            'to' => [[
                'type' => 'phone',
                'number' => '15555555555'
            ]],
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

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('create-outbound-call-success', '201'));

        $outboundCall = (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setEventWebhook(new Webhook('http://domain.test/event'))
            ->setAnswerWebhook(new Webhook('http://domain.test/answer'))
            ->setRingingTimer($payload['ringing_timer'])
            ->setLengthTimer($payload['length_timer'])
            ->setMachineDetection(OutboundCall::MACHINE_HANGUP)
        ;
        $callData = $this->voiceClient->createOutboundCall($outboundCall);

        $this->assertTrue($callData instanceof Event);
        $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $callData->getUuid());
        $this->assertEquals('started', $callData->getStatus());
        $this->assertEquals('outbound', $callData->getDirection());
        $this->assertEquals('2541d01c-253e-48be-a8e0-da4bbe4c3722', $callData->getConversationUuid());
    }
    
    public function testCanCreateOutboundCallWithNCCO()
    {
        $payload = [
            'to' => [[
                'type' => 'phone',
                'number' => '15555555555'
            ]],
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
                ]
            ],
            'length_timer' => '7200',
            'ringing_timer' => '60'
        ];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('create-outbound-call-success', '201'));

        $outboundCall = (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setEventWebhook(new Webhook('http://domain.test/event'))
            ->setNCCO((new NCCO)->addAction(new Talk('Thank you for trying Vonage')))
            ->setLengthTimer(7200)
            ->setRingingTimer(60)
        ;
        $callData = $this->voiceClient->createOutboundCall($outboundCall);

        $this->assertTrue($callData instanceof Event);
        $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $callData->getUuid());
        $this->assertEquals('started', $callData->getStatus());
        $this->assertEquals('outbound', $callData->getDirection());
        $this->assertEquals('2541d01c-253e-48be-a8e0-da4bbe4c3722', $callData->getConversationUuid());
    }

    public function testCanHandleErrorWhileCreatingOutboundCall()
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage('Bad Request');

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            return true;
        }))->willReturn($this->getResponse('error_vapi', 400));

        $outboundCall = new OutboundCall(new Phone('15555555555'), new Phone('16666666666'));
        $this->voiceClient->createOutboundCall($outboundCall);
    }

    public function testCreateOutboundCallErrorUnknownFormat()
    {
        $this->expectException(Request::class);
        $this->expectExceptionMessage("Unexpected error");

        $this->vonageClient->send(Argument::that(function () {
            return true;
        }))->willReturn($this->getResponse('error_unknown_format', '400'))
        ;

        $outboundCall = new OutboundCall(new Phone('15555555555'), new Phone('16666666666'));
        $this->voiceClient->createOutboundCall($outboundCall);
    }

    public function testCanRetrieveCallInformation()
    {
        $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('call', 200));

        $call = $this->voiceClient->get($id);

        $this->assertInstanceOf(Call::class, $call);
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

    public function testCanModifyACallLeg()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'earmuff'];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $this->voiceClient->modifyCall($id, CallAction::EARMUFF);
    }

    public function testCanEarmuffCall()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'earmuff'];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $this->voiceClient->earmuffCall($id);
    }

    public function testCanUnearmuffCall()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'unearmuff'];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('empty', 204));
        
        $this->voiceClient->unearmuffCall($id);
    }

    public function testCanMuteCall()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'mute'];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('empty', 204));
        
        $this->voiceClient->muteCall($id);
    }

    public function testCanUnmuteCall()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'unmute'];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('empty', 204));
        
        $this->voiceClient->unmuteCall($id);
    }

    public function testCanHangupCall()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['action' => 'hangup'];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $this->voiceClient->hangupCall($id);
    }

    public function testCanTransferACallLegWithNCCO()
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
                    ]
                ]
            ],
        ];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $ncco = (new NCCO)
            ->addAction(new Talk('Thank you for trying Vonage'))
        ;

        $this->voiceClient->transferCallWithNCCO($id, $ncco);
    }

    public function testCanTransferACallLegWithURL()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => ['https://test.domain/transfer.json'],
            ],
        ];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $this->voiceClient->transferCallWithUrl($id, 'https://test.domain/transfer.json');
    }

    public function testcanStreamAudioIntoCall()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $url = 'http://domain.test/music.mp3';
        $payload = [
            'stream_url' => [$url],
            'loop' => '1',
            'level' => '0',
        ];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/stream', 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('stream'));

        
        $response = $this->voiceClient->streamAudio($id, $url);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('Stream started', $response['message']);
    }

    public function testCanStopStreamingAudioIntoCall()
    {
        $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/stream', 'DELETE', $request);
            return true;
        }))->willReturn($this->getResponse('stream-stopped'));

        
        $response = $this->voiceClient->stopStreamAudio($id);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('Stream stopped', $response['message']);
    }

    public function testCanPlayTTSIntoCall()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = [
            'text' => 'This is sample text',
        ];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/talk', 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('talk'));

        $action = new Talk('This is sample text');
        $response = $this->voiceClient->playTTS($id, $action);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('Talk started', $response['message']);
    }

    public function testCanStopTTSInCall()
    {
        $id = '63f61863-4a51-4f6b-86e1-46edebcf9356';

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/talk', 'DELETE', $request);
            return true;
        }))->willReturn($this->getResponse('talk-stopped'));

        
        $response = $this->voiceClient->stopTTS($id);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('Talk stopped', $response['message']);
    }

    public function testCanPlayDTMFIntoCall()
    {
        $id = 'ssf61863-4a51-ef6b-11e1-w6edebcf93bb';
        $payload = ['digits' => '1492'];

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id . '/dtmf', 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('dtmf'));

        $response = $this->voiceClient->playDTMF($id, $payload['digits']);

        $this->assertEquals($id, $response['uuid']);
        $this->assertEquals('DTMF sent', $response['message']);
    }

    public function testCanSearchCalls()
    {
        $response = $this->getResponse('search');
        $data = json_decode($response->getBody()->getContents(), true);
        $response->getBody()->rewind();
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'GET', $request);
            $this->assertRequestQueryContains('page_size', '10', $request);
            $this->assertRequestQueryContains('record_index', '0', $request);
            $this->assertRequestQueryContains('order', 'asc', $request);
            $this->assertRequestQueryContains('status', VoiceFilter::STATUS_STARTED, $request);
            return true;
        }))->willReturn($response);

        $filter = new VoiceFilter();
        $filter->setStatus(VoiceFilter::STATUS_STARTED);

        $response = $this->voiceClient->search($filter);

        $this->assertCount(1, $response);
        $call = $response->current();

        $this->assertEquals($data['_embedded']['calls'][0]['uuid'], $call->getUuid());
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success', $status = 200)
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'), (int) $status);
    }
}
