<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\User;

use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\Exception as ClientException;
use VonageTest\Psr7AssertionTrait;
use Vonage\User\Collection;
use Vonage\User\User;

use function fopen;
use function json_encode;

class CollectionTest extends TestCase
{
    use Psr7AssertionTrait;

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

        /** @noinspection PhpParamsInspection */
        $this->collection->setClient($this->vonageClient->reveal());
    }

    /**
     * Getting an entity from the collection should not fetch it if we use the array interface.
     *
     * @dataProvider getUser
     *
     * @param $payload
     * @param $id
     */
    public function testArrayIsLazy($payload, $id): void
    {
        $this->vonageClient->send(Argument::any())->willReturn($this->getResponse('user'));

        $user = $this->collection[$payload];

        $this->assertInstanceOf(User::class, $user);
        $this->vonageClient->send(Argument::any())->shouldNotHaveBeenCalled();
        $this->assertEquals($id, $user->getId());

        if ($payload instanceof User) {
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
     *
     * @param $payload
     * @param $id
     *
     * @throws ClientException\Exception
     * @throws ClientException\Request
     * @throws ClientException\Server
     * @throws ClientExceptionInterface
     */
    public function testGetIsNotLazy($payload, $id): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($id) {
            $this->assertRequestUrl('api.nexmo.com', '/beta/users/' . $id, 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('user'))->shouldBeCalled();

        $user = $this->collection->get($payload);

        if ($payload instanceof User) {
            $this->assertSame($payload, $user);
        }
    }

    public function testCanFetchAllUsers(): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
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
     *
     * @param $payload
     * @param $method
     */
    public function testCreatePostConversation($payload, $method): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);

            return true;
        }))->willReturn($this->getResponse('user', 200));

        $user = $this->collection->$method($payload);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('USR-aaaaaaaa-bbbb-cccc-dddd-0123456789ab', $user->getId());
    }

    /**
     * @dataProvider postUser
     *
     * @param $payload
     * @param $method
     */
    public function testCreatePostUserErrorFromStitch($payload, $method): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);

            return true;
        }))->willReturn($this->getResponse('error_stitch', 400));

        try {
            $this->collection->$method($payload);

            self::fail('Expected to throw request exception');
        } catch (ClientException\Request $e) {
            $this->assertEquals('the token was rejected', $e->getMessage());
        }
    }

    /**
     * @dataProvider postUser
     *
     * @param $payload
     * @param $method
     */
    public function testCreatePostUserErrorFromProxy($payload, $method): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);

            return true;
        }))->willReturn($this->getResponse('error_proxy', 400));

        try {
            $this->collection->$method($payload);

            self::fail('Expected to throw request exception');
        } catch (ClientException\Request $e) {
            $this->assertEquals('Unsupported Media Type', $e->getMessage());
        }
    }

    /**
     * @dataProvider postUser
     *
     * @param $payload
     * @param $method
     */
    public function testCreatePostCallErrorUnknownFormat($payload, $method): void
    {
        $this->vonageClient->send(Argument::that(function (RequestInterface $request) use ($payload) {
            $this->assertRequestUrl('api.nexmo.com', '/beta/users', 'POST', $request);
            $this->assertRequestBodyIsJson(json_encode($payload), $request);
            return true;
        }))->willReturn($this->getResponse('error_unknown_format', 400));

        try {
            $this->collection->$method($payload);

            self::fail('Expected to throw request exception');
        } catch (ClientException\Request $e) {
            $this->assertEquals("Unexpected error", $e->getMessage());
        }
    }

    /**
     * Getting a user can use an object or an ID.
     */
    public function getUser(): array
    {
        return [
            ['3fd4d839-493e-4485-b2a5-ace527aacff3', '3fd4d839-493e-4485-b2a5-ace527aacff3'],
            [new User('3fd4d839-493e-4485-b2a5-ace527aacff3'), '3fd4d839-493e-4485-b2a5-ace527aacff3']
        ];
    }

    /**
     * Creating a user can take a Call object or a simple array.
     */
    public function postUser(): array
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
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
