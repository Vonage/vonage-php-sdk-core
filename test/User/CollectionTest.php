<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Users;

use Vonage\Client;
use Vonage\User\User;
use Vonage\User\Collection;
use VonageTest\Psr7AssertionTrait;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Response;
use Vonage\Client\Exception;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    use Psr7AssertionTrait;

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
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->collection = new Collection();
        $this->collection->setClient($this->vonageClient->reveal());
    }

    /**
     * Getting an entity from the collection should not fetch it if we use the array interface.
     *
     * @dataProvider getUser
     */
    public function testArrayIsLazy($payload, $id)
    {
        $this->vonageClient->send(Argument::any())->willReturn($this->getResponse('user'));

        $user = $this->collection[$payload];

        $this->assertInstanceOf(User::class, $user);
        $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();
        $this->assertEquals($id, $user->getId());

        if($payload instanceof User){
            $this->assertSame($payload, $user);
        }

        // Once we call get() the rest of the data should be populated
        $user->get();
        $this->vonageClient->send(Argument::any())->shouldHaveBeenCalled();
    }

    /**
     * Using `get()` should fetch the user data. Will accept both a string id and an object. Must return the same object
     * if that's the input.
     *
     * @dataProvider getUser
     */
    public function testGetIsNotLazy($payload, $id)
    {
        $this->vonageClient->send(Argument::that(function(RequestInterface $request) use ($id){
            $this->assertRequestUrl('api.nexmo.com', '/beta/users/' . $id, 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('user'))->shouldBeCalled();

        $user = $this->collection->get($payload);

        $this->assertInstanceOf(User::class, $user);
        if($payload instanceof User){
            $this->assertSame($payload, $user);
        }
    }

    public function testCanFetchAllUsers()
    {
        $this->vonageClient->send(Argument::that(function(RequestInterface $request){
            $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('multiple_users'))->shouldBeCalled();

        $users = $this->collection->fetch();

        $this->assertCount(3, $users);
        $this->assertInstanceOf(User::class, $users[0]);
        $this->assertInstanceOf(User::class, $users[1]);
        $this->assertInstanceOf(User::class, $users[2]);
    }

    /**
     * @dataProvider postUser
     */
    public function testCreatePostConversation($payload, $method)
    {
        $this->vonageClient->send(Argument::that(function(RequestInterface $request) use ($payload){
            $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('user', '200'));

        $user = $this->collection->$method($payload);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('USR-aaaaaaaa-bbbb-cccc-dddd-0123456789ab', $user->getId());
    }
    
    /**
     * @dataProvider postUser
     */
    public function testCreatePostUserErrorFromStitch($payload, $method)
    {
        $this->vonageClient->send(Argument::that(function(RequestInterface $request) use ($payload){
            $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_stitch', '400'));

        try {
            $this->collection->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), 'the token was rejected');
        }
    }

    /**
     * @dataProvider postUser
     */
    public function testCreatePostUserErrorFromProxy($payload, $method)
    {
        $this->vonageClient->send(Argument::that(function(RequestInterface $request) use ($payload){
            $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_proxy', '400'));

        try {
            $user = $this->collection->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), 'Unsupported Media Type');
        }
    }

    /**
     * @dataProvider postUser
     */
    public function testCreatePostCallErrorUnknownFormat($payload, $method)
    {
        $this->vonageClient->send(Argument::that(function(RequestInterface $request) use ($payload){
            $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_unknown_format', '400'));

        try {
            $user = $this->collection->$method($payload);
            $this->fail('Expected to throw request exception');
        } catch (Exception\Request $e) {
            $this->assertEquals($e->getMessage(), "Unexpected error");
        }
    }

    /**
     * Getting a user can use an object or an ID.
     *
     * @return array
     */
    public function getUser()
    {
        return [
            ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
            [new User('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
        ];
    }

    /**
     * Creating a user can take a Call object or a simple array.
     * @return array
     */
    public function postUser()
    {
        $raw = [
            'name' => 'demo',
        ];

        $user = new User();
        $user->setName('demo');

        return [
            [clone $user, 'create'],
            [clone $user, 'post'],
            [$raw, 'create'],
            [$raw, 'post'],
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
