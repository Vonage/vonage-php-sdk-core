<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Call;

use Nexmo\Call\Dtmf;
use NexmoTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class DtmfTest extends TestCase
{
    use Psr7AssertionTrait;

    protected $id;

    /**
     * @var Dtmf
     */
    protected $entity;

    /**
     * @var Dtmf
     */
    protected $new;

    protected $class;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $nexmoClient;

    public function setUp()
    {
        $this->id = '3fd4d839-493e-4485-b2a5-ace527aacff3';
        $this->class = Dtmf::class;

        $this->entity = new Dtmf('3fd4d839-493e-4485-b2a5-ace527aacff3');
        $this->new = new Dtmf();

        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->entity->setClient($this->nexmoClient->reveal());
        $this->new->setClient($this->nexmoClient->reveal());
    }

    public function testHasId()
    {
        $this->assertSame($this->id, $this->entity->getId());
    }

    /**
     * @param $value
     * @param $param
     * @param $setter
     * @param $expected
     * @dataProvider setterParameters
     */
    public function testSetParams($value, $param, $setter, $expected)
    {
        $this->entity->$setter($value);
        $data = $this->entity->jsonSerialize();
        $this->assertEquals($expected, $data[$param]);
    }

    /**
     * @param $value
     * @param $param
     * @dataProvider setterParameters
     */
    public function testArrayParams($value, $param)
    {
        $this->entity[$param] = $value;

        $data = $this->entity->jsonSerialize();
        $this->assertEquals($value, $data[$param]);
    }

    public function setterParameters()
    {
        return [
            ['1234', 'digits', 'setDigits', '1234']
        ];
    }

    public function testPutMakesRequest()
    {
        $this->entity->setDigits('3119');

        $callId = $this->id;
        $entity = $this->entity;

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($callId, $entity){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/dtmf', 'PUT', $request);
            $expected = json_decode(json_encode($entity), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);
            return true;
        }))->willReturn($this->getResponse('dtmf', '200'));

        $event = $this->entity->put();

        $this->assertInstanceOf('Nexmo\Call\Event', $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('DTMF sent', $event['message']);
    }

    public function testPutCanReplace()
    {
        $class = $this->class;

        $entity = new $class;
        $entity->setDigits('1234');

        $callId = $this->id;

        $this->nexmoClient->send(Argument::that(function(RequestInterface $request) use ($callId, $entity){
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $callId . '/dtmf', 'PUT', $request);
            $expected = json_decode(json_encode($entity), true);

            $request->getBody()->rewind();
            $body = json_decode($request->getBody()->getContents(), true);
            $request->getBody()->rewind();

            $this->assertEquals($expected, $body);
            return true;
        }))->willReturn($this->getResponse('dtmf', '200'));

        $event = $this->entity->put($entity);

        $this->assertInstanceOf('Nexmo\Call\Event', $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('DTMF sent', $event['message']);
    }

    public function testInvokeProxiesPutWithArgument()
    {
        $object = $this->entity;

        $this->nexmoClient->send(Argument::any())->willReturn($this->getResponse('dtmf', '200'));
        $test = $object();
        $this->assertSame($this->entity, $test);

        $this->nexmoClient->send(Argument::any())->shouldNotHaveBeenCalled();

        $class = $this->class;
        $entity = new $class();
        $entity->setDigits(1234);

        $event = $object($entity);

        $this->assertInstanceOf('Nexmo\Call\Event', $event);
        $this->assertSame('ssf61863-4a51-ef6b-11e1-w6edebcf93bb', $event['uuid']);
        $this->assertSame('DTMF sent', $event['message']);

        $this->nexmoClient->send(Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * Get the API response we'd expect for a call to the API.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success', $status = 200)
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'), $status);
    }
}
