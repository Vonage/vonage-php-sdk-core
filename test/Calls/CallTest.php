<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Calls;

use Nexmo\Calls\Call;
use Nexmo\Calls\Endpoint;
use Nexmo\Calls\Update\Transfer;
use Nexmo\Calls\Webhook;
use Prophecy\Argument;
use EnricoStahn\JsonAssert\Assert as JsonAssert;

class CallTest extends \PHPUnit_Framework_TestCase
{
    use JsonAssert;

    /**
     * @var Call
     */
    protected $call;

    protected $class;

    public function setUp()
    {
        $this->call = new Call();
        $this->class = Call::class;
    }

    /**
     * Entities should be constructable with an ID.
     */
    public function testConstructWithId()
    {
        $class = $this->class;
        $entity = new $class('3fd4d839-493e-4485-b2a5-ace527aacff3');
        $this->assertSame('3fd4d839-493e-4485-b2a5-ace527aacff3', $entity->getId());
    }

    public function testGetProxiesCollection()
    {
        $collection = $this->prophesize('Nexmo\Calls\Collection');
        $this->call->setCollection($collection->reveal());
        $collection->get(Argument::is($this->call))
                   ->shouldBeCalledTimes(1)
                   ->willReturn($this->call);

        $return = $this->call->get();
        $this->assertSame($this->call, $return);
    }

    public function testPutProxiesCollection()
    {
        $collection = $this->prophesize('Nexmo\Calls\Collection');
        $this->call->setCollection($collection->reveal());
        $transfer = new Transfer('http://example.com');
        $collection->put(Argument::is($transfer), Argument::is($this->call))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->call);

        $return = $this->call->put($transfer);
        $this->assertSame($this->call, $return);
    }

    public function testLazyLoad()
    {
        $collection = $this->prophesize('Nexmo\Calls\Collection');

        $call = new Call('id');
        $call->setCollection($collection->reveal());

        $collection->get(Argument::is($call))
            ->shouldBeCalledTimes(1)
            ->will(function() use ($call){
                $data = json_decode(file_get_contents(__DIR__ . '/responses/call.json'), true);
                $call->JsonUnserialize($data);
            });

        $return = $call->getStatus();
        $this->assertSame('completed', $return);
    }

    //split into discrete tests, use trait as can be useful elsewhere for consistency
    public function testToIsSet()
    {
        $this->call->setTo('14845551212');
        $this->assertSame('14845551212', (string) $this->call->getTo());
        $this->assertSame('14845551212', $this->call->getTo()->getId());
        $this->assertSame('phone', $this->call->getTo()->getType());

        $data = $this->call->jsonSerialize();

        $this->assertArrayHasKey('to', $data);
        $this->assertInternalType('array', $data['to']);
        $this->assertArrayHasKey('number', $data['to'][0]);
        $this->assertArrayHasKey('type', $data['to'][0]);
        $this->assertEquals('14845551212', $data['to'][0]['number']);
        $this->assertEquals('phone', $data['to'][0]['type']);

        $this->call->setTo(new Endpoint('14845551212'));
        $this->assertSame('14845551212', (string) $this->call->getTo());
        $this->assertSame('14845551212', $this->call->getTo()->getId());
        $this->assertSame('phone', $this->call->getTo()->getType());

        $data = $this->call->jsonSerialize();

        $this->assertArrayHasKey('to', $data);
        $this->assertInternalType('array', $data['to']);
        $this->assertArrayHasKey('number', $data['to'][0]);
        $this->assertArrayHasKey('type', $data['to'][0]);
        $this->assertEquals('14845551212', $data['to'][0]['number']);
        $this->assertEquals('phone', $data['to'][0]['type']);
    }

    public function testFromIsSet()
    {
        $this->call->setFrom('14845551212');
        $this->assertSame('14845551212', (string) $this->call->getFrom());
        $this->assertSame('14845551212', $this->call->getFrom()->getId());
        $this->assertSame('phone', $this->call->getFrom()->getType());

        $data = $this->call->jsonSerialize();

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('number', $data['from']);
        $this->assertArrayHasKey('type', $data['from']);
        $this->assertEquals('14845551212', $data['from']['number']);
        $this->assertEquals('phone', $data['from']['type']);

        $this->call->setFrom(new Endpoint('14845551212'));
        $this->assertSame('14845551212', (string) $this->call->getFrom());
        $this->assertSame('14845551212', $this->call->getFrom()->getId());
        $this->assertSame('phone', $this->call->getFrom()->getType());

        $data = $this->call->jsonSerialize();

        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('number', $data['from']);
        $this->assertArrayHasKey('type', $data['from']);
        $this->assertEquals('14845551212', $data['from']['number']);
        $this->assertEquals('phone', $data['from']['type']);
    }

    public function testWebhooks()
    {
        $this->call->setWebhook(Call::WEBHOOK_ANSWER, 'http://example.com');

        $data = $this->call->jsonSerialize();
        $this->assertArrayHasKey('answer_url', $data);
        $this->assertCount(1, $data['answer_url']);
        $this->assertEquals('http://example.com', $data['answer_url'][0]);

        $this->call->setWebhook(new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com'));

        $data = $this->call->jsonSerialize();
        $this->assertArrayHasKey('answer_url', $data);
        $this->assertCount(1, $data['answer_url']);
        $this->assertEquals('http://example.com', $data['answer_url'][0]);

        $this->call->setWebhook(new Webhook(Call::WEBHOOK_ANSWER, ['http://example.com', 'http://example.com/test']));

        $data = $this->call->jsonSerialize();
        $this->assertArrayHasKey('answer_url', $data);
        $this->assertCount(2, $data['answer_url']);
        $this->assertEquals('http://example.com', $data['answer_url'][0]);
        $this->assertEquals('http://example.com/test', $data['answer_url'][1]);

        $this->call->setWebhook(new Webhook(Call::WEBHOOK_ANSWER, 'http://example.com', 'POST'));

        $data = $this->call->jsonSerialize();
        $this->assertArrayHasKey('answer_method', $data);
        $this->assertEquals('POST', $data['answer_method']);
    }

    public function testTimers()
    {
        $this->call->setTimer(Call::TIMER_LENGTH, 10);

        $data = $this->call->jsonSerialize();
        $this->assertArrayHasKey('length_timer', $data);
        $this->assertEquals(10, $data['length_timer']);
    }

    public function testTimeouts()
    {
        $this->call->setTimeout(Call::TIMEOUT_MACHINE, 10);

        $data = $this->call->jsonSerialize();
        $this->assertArrayHasKey('machine_timeout', $data);
        $this->assertEquals(10, $data['machine_timeout']);
    }

    public function testHydrate()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/call.json'), true);
        $this->call->JsonUnserialize($data);

        $this->assertEquals('phone', $this->call->getTo()->getType());
        $this->assertEquals('phone', $this->call->getFrom()->getType());

        $this->assertEquals('14845552194', $this->call->getTo()->getId());
        $this->assertEquals('14841113423', $this->call->getFrom()->getId());

        $this->assertEquals('14845552194', $this->call->getTo()->getNumber());
        $this->assertEquals('14841113423', $this->call->getFrom()->getNumber());

        $this->assertEquals('3fd4d839-493e-4485-b2a5-ace527aacff3', $this->call->getId());
        $this->assertEquals('completed', $this->call->getStatus());
        $this->assertEquals('outbound', $this->call->getDirection());

        $this->assertInstanceOf('Nexmo\Conversations\Conversation', $this->call->getConversation());
        $this->assertEquals('0f9f56dd-9c90-4fd0-a40e-d075f009d2ee', $this->call->getConversation()->getId());
    }
}
