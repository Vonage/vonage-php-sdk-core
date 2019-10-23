<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Users;

use Nexmo\Client;
use Nexmo\User\User;
use Prophecy\Argument;
use Nexmo\User\Hydrator;
use Nexmo\User\Collection;
use Nexmo\Client\Exception;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Nexmo\Client\OpenAPIResource;
use NexmoTest\Psr7AssertionTrait;
use Nexmo\User\Client as UserClient;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    protected $nexmoClient;

    /**
     * @var UserClient
     */
    protected $collection;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize(Client::class);
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->openAPI = new OpenAPIResource();
        $this->openAPI->setClient($this->nexmoClient->reveal());
        $this->openAPI->setBaseUri('/v0.1/users');
        $this->openAPI->setCollectionName('users');
        $this->openAPI->setCollectionPrototype(new Collection());
        
        $this->collection = new UserClient($this->openAPI, new Hydrator());
        $this->collection->setClient($this->nexmoClient->reveal());
    }

    /**
     * Using `get()` should fetch the user data. Will accept both a string id.
     *
     * @dataProvider getUser
     */
    public function testGetIsNotLazy(User $payload, string $id)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/v0.1/users/' . $id, 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('user'))->shouldBeCalled();

        $user = $this->collection->get($payload);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($payload->getId(), $user->getId());
        
    }

    public function testCanFetchAllUsers()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/v0.1/users', 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('multiple_users'))->shouldBeCalled();

        $users = $this->collection->fetch();

        $this->assertCount(3, $users);
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    /**
     * @dataProvider postUser
     */
    public function testCreatePostUser($payload, $method)
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v0.1/users', 'POST', $request);
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
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v0.1/users', 'POST', $request);
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
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v0.1/users', 'POST', $request);
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
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/v0.1/users', 'POST', $request);
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
            [new User('USR-aaaaaaaa-bbbb-cccc-dddd-0123456789ab'), 'USR-aaaaaaaa-bbbb-cccc-dddd-0123456789ab']
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
