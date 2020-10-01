<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Calls;

use Vonage\Call\Hydrator;
use Vonage\Call\Call;
use Vonage\Call\Filter;
use Prophecy\Argument;
use Vonage\Call\Transfer;
use Vonage\Call\Collection;
use Vonage\Client\Exception;
use Zend\Diactoros\Response;
use Vonage\Client\APIResource;
use PHPUnit\Framework\TestCase;
use VonageTest\Psr7AssertionTrait;
use Psr\Http\Message\RequestInterface;

class CollectionTest extends TestCase
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
     * @var Collection
     */
    protected $collection;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize('Vonage\Client');
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->collection = @new Collection();
        $this->collection->setClient($this->vonageClient->reveal());
    }

    /**
     * Collection can be invoked as a method. This allows a fluent inerface from the main client. When invoked with a
     * filter, the collection should use that filter.
     *
     *     $Vonage->calls($filter)
     */
    public function testInvokeWithFilter()
    {
        $collection = $this->collection;
        $filter = @new Filter();
        $return = @$collection($filter);

        $this->assertSame($collection, $return);
        $this->assertSame($collection->getFilter(), $filter);
    }

    /**
     * Hydrate is used by the common collection paging.
     */
    public function testHydrateSetsDataAndClient()
    {
        $call = $this->prophesize('Vonage\Call\Call');

        $data = ['test' => 'data'];

        $this->collection->hydrateEntity($data, $call->reveal());

        $call->setClient($this->vonageClient->reveal())->shouldHaveBeenCalled();
        $call->jsonUnserialize($data)->shouldHaveBeenCalled();
    }

    /**
     * Getting an entity from the collection should not fetch it if we use the array interface.
     *
     * @dataProvider getCall
     */
    public function testArrayIsLazy($payload, $id)
    {
        //not testing the call resource, just making sure it uses the same client as the collection
        $this->vonageClient->send(Argument::any())->willReturn($this->getResponse('call'));

        $collection = $this->collection;
        $call = @$collection[$payload];

        $this->assertInstanceOf('Vonage\Call\Call', $call);
        $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();
        $this->assertEquals($id, $call->getId());

        if ($payload instanceof Call) {
            $this->assertSame($payload, $call);
        }

        @$call->get();
        $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * Using `get()` should fetch the call data. Will accept both a string id and an object. Must return the same object
     * if that's the input.
     *
     * @dataProvider getCall
     */
    public function testGetIsNotLazy($payload, $id)
    {
        //this generally proxies the call resource, but we're testing the correct request, not the proxy
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $id, 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('call'))->shouldBeCalled();

        $call = @$this->collection->get($payload);

        $this->assertInstanceOf('Vonage\Call\Call', $call);
        if ($payload instanceof Call) {
            $this->assertSame($payload, $call);
        }
    }

    /**
     * @dataProvider postCall
     */
    public function testCreatePostCall($payload, $method)
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('created', '201'));

        $call = @$this->collection->$method($payload);

        $this->assertInstanceOf('Vonage\Call\Call', $call);
        $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $call->getId());
    }

    /**
     * @dataProvider postCallNcco
     */
    public function testCreateCallNcco($payload)
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $ncco = [['action' => 'talk', 'text' => 'Hello World']];

            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            $this->assertRequestJsonBodyContains('ncco', $ncco, $request);
            return true;
        }))->willReturn($this->getResponse('created', '201'));

        $call = @$this->collection->create($payload);

        $this->assertInstanceOf('Vonage\Call\Call', $call);
        $this->assertEquals('e46fd8bd-504d-4044-9600-26dd18b41111', $call->getId());
    }
    
    /**
     * @dataProvider postCall
     */
    public function testCreatePostCallErrorFromVApi($payload, $method)
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_vapi', '400'));

        try {
            $call = @$this->collection->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), 'Bad Request');
        }
    }

    /**
     * @dataProvider postCall
     */
    public function testCreatePostCallErrorFromProxy($payload, $method)
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_proxy', '400'));

        try {
            $call = @$this->collection->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), 'Unsupported Media Type');
        }
    }

    /**
     * @dataProvider postCall
     */
    public function testCreatePostCallErrorUnknownFormat($payload, $method)
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_unknown_format', '400'));

        try {
            $call = @$this->collection->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), "Unexpected error");
        }
    }

    /**
     * @dataProvider putCall
     */
    public function testPutCall($expectedId, $id, $payload)
    {
        //this generally proxies the call resource, but we're testing the correct request, not the proxy
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($expectedId, $payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v1/calls/' . $expectedId, 'PUT', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('updated'))->shouldBeCalled();

        $call = @$this->collection->put($payload, $id);
        $this->assertInstanceOf('Vonage\Call\Call', $call);

        if ($id instanceof Call) {
            $this->assertSame($id, $call);
        } else {
            $this->assertEquals($id, $call->getId());
        }
    }

    /**
     * Getting a call can use an object or an ID.
     *
     * @return array
     */
    public function getCall()
    {
        return [
            ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
            [@new Call('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
        ];
    }

    /**
     * Creating a call with an NCCO can take a Call object or a simple array.
     * @return array
     */
    public function postCallNcco()
    {
        $raw = [
            'to' => [[
                'type' => 'phone',
                'number' => '14843331234'
            ]],
            'from' => [
                'type' => 'phone',
                'number' => '14843335555'
            ],
            'ncco' => [
                [
                    'action' => 'talk',
                    'text' => 'Hello World'
                ]
            ]
        ];


        $call = @new Call();
        @$call->setTo('14843331234')
             ->setFrom('14843335555')
             ->setNcco([
                 [
                     'action' => 'talk',
                     'text' => 'Hello World'
                 ]
             ]);

        return [
            'object' => [clone $call],
            'array' => [$raw]
        ];
    }
    /**
     * Creating a call can take a Call object or a simple array.
     * @return array
     */
    public function postCall()
    {
        $raw = [
            'to' => [[
                'type' => 'phone',
                'number' => '14843331234'
            ]],
            'from' => [
                'type' => 'phone',
                'number' => '14843335555'
            ],
            'answer_url' => ['https://example.com/answer'],
            'event_url' => ['https://example.com/event'],
            'answer_method' => 'POST',
            'event_method' => 'POST'
        ];


        $call = @new Call();
        @$call->setTo('14843331234')
             ->setFrom('14843335555')
             ->setWebhook(@Call::WEBHOOK_ANSWER, 'https://example.com/answer', 'POST')
             ->setWebhook(@Call::WEBHOOK_EVENT, 'https://example.com/event', 'POST');

        return [
            [clone $call, 'create'],
            [clone $call, 'post'],
            [$raw, 'create'],
            [$raw, 'post'],
        ];
    }

    /**
     * Can update the call with an object or a raw array.
     * @return array
     */
    public function putCall()
    {
        $id = '1234abcd';
        $payload = [
            'action' => 'transfer',
            'destination' => [
                'type' => 'ncco',
                'url' => ['http://example.com']
            ]
        ];

        $call = @new Call($id);
        $transfer = @new Transfer('http://example.com');

        return [
            [$id, $id, $payload],
            [$id, $call, $payload],
            [$id, $id, $transfer],
            [$id, $call, $transfer]
        ];
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
