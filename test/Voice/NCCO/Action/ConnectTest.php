<?php
declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use Vonage\Voice\Endpoint\Phone;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\Endpoint\EndpointInterface;
use Vonage\Voice\NCCO\Action\Connect;
use Vonage\Voice\Webhook;

class ConnectTest extends TestCase
{
    /**
     * @var EndpointInterface
     */
    protected $endpoint;

    public function setUp(): void
    {
        $this->endpoint = new Phone('15551231234');
    }

    public function testSimpleSetup()
    {
        $expected = [
            'action' => 'connect',
            'endpoint' => [
                [
                    'type' => 'phone',
                    'number' => '15551231234'
                ]
            ]
        ];

        $action = new Connect($this->endpoint);

        $this->assertSame($expected, $action->toNCCOArray());
    }

    public function testCanSetAdditionalInformation()
    {
        $webhook = new Webhook('https://test.domain/events');

        $action = new Connect($this->endpoint);
        $action
            ->setFrom('15553216547')
            ->setMachineDetection(Connect::MACHINE_CONTINUE)
            ->setEventType(Connect::EVENT_TYPE_SYNCHRONOUS)
            ->setLimit(6000)
            ->setRingbackTone('https://test.domain/ringback.mp3')
            ->setTimeout(10)
            ->setEventWebhook($webhook)
        ;

        $this->assertSame('15553216547', $action->getFrom());
        $this->assertSame(Connect::MACHINE_CONTINUE, $action->getMachineDetection());
        $this->assertSame(Connect::EVENT_TYPE_SYNCHRONOUS, $action->getEventType());
        $this->assertSame(6000, $action->getLimit());
        $this->assertSame('https://test.domain/ringback.mp3', $action->getRingbackTone());
        $this->assertSame(10, $action->getTimeout());
        $this->assertSame($webhook, $action->getEventWebhook());
    }

    public function testGeneratesCorrectNCCOArray()
    {
        $webhook = new Webhook('https://test.domain/events');

        $action = new Connect($this->endpoint);
        $action
            ->setFrom('15553216547')
            ->setMachineDetection(Connect::MACHINE_CONTINUE)
            ->setEventType(Connect::EVENT_TYPE_SYNCHRONOUS)
            ->setLimit(6000)
            ->setRingbackTone('https://test.domain/ringback.mp3')
            ->setTimeout(10)
            ->setEventWebhook($webhook)
        ;

        $ncco = $action->toNCCOArray();

        $this->assertSame('15553216547', $ncco['from']);
        $this->assertSame(Connect::MACHINE_CONTINUE, $ncco['machineDetection']);
        $this->assertSame(Connect::EVENT_TYPE_SYNCHRONOUS, $ncco['eventType']);
        $this->assertSame(6000, $ncco['limit']);
        $this->assertSame('https://test.domain/ringback.mp3', $ncco['ringbackTone']);
        $this->assertSame(10, $ncco['timeout']);
        $this->assertSame(['https://test.domain/events'], $ncco['eventUrl']);
        $this->assertSame('POST', $ncco['eventMethod']);
    }

    public function testJSONSerializesToCorrectStructure()
    {
        $webhook = new Webhook('https://test.domain/events');

        $action = new Connect($this->endpoint);
        $action
            ->setFrom('15553216547')
            ->setMachineDetection(Connect::MACHINE_CONTINUE)
            ->setEventType(Connect::EVENT_TYPE_SYNCHRONOUS)
            ->setLimit(6000)
            ->setRingbackTone('https://test.domain/ringback.mp3')
            ->setTimeout(10)
            ->setEventWebhook($webhook)
        ;

        $ncco = $action->jsonSerialize();

        $this->assertSame('15553216547', $ncco['from']);
        $this->assertSame(Connect::MACHINE_CONTINUE, $ncco['machineDetection']);
        $this->assertSame(Connect::EVENT_TYPE_SYNCHRONOUS, $ncco['eventType']);
        $this->assertSame(6000, $ncco['limit']);
        $this->assertSame('https://test.domain/ringback.mp3', $ncco['ringbackTone']);
        $this->assertSame(10, $ncco['timeout']);
        $this->assertSame(['https://test.domain/events'], $ncco['eventUrl']);
        $this->assertSame('POST', $ncco['eventMethod']);
    }

    public function testInvalidMachineDetectionThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Uknown machine detection type');

        $action = new Connect($this->endpoint);
        $action->setMachineDetection('foo');
    }

    public function testInvalidEventTypeThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown event type for Connection action');

        $action = new Connect($this->endpoint);
        $action->setEventType('foo');
    }
}
