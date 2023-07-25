<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Users;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Vonage\Users\Client as UsersClient;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Users\Hydrator;
use Vonage\Users\User;
use VonageTest\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

use function fopen;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;

    protected Client|ObjectProphecy $vonageClient;

    protected APIResource $apiClient;

    protected UsersClient $usersClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Container(new Client\Credentials\Basic('abc', 'def'))
        );

        $apiResource = new APIResource();
        $apiResource->setClient($this->vonageClient->reveal())
            ->setBaseUri('/v1/users')
            ->setCollectionName('users');

        $this->usersClient = new UsersClient($apiResource, new Hydrator());

        /** @noinspection PhpParamsInspection */
        $this->usersClient->setClient($this->vonageClient->reveal());
    }

    public function testWillListUsers(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v1/users?page_size=10&order=asc',
                $uriString
            );

            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('list-user-success'));

        $response = $this->usersClient->listUsers(10, 'asc');

        foreach ($response as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    public function testWillCreateUser(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v1/users',
                $uriString
            );

            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('create-user-success'));

        $user = new User();

        $userData = [
            "name" => "my_user_name",
            "display_name" => "My User Name",
            "image_url" => "https://example.com/image.png",
            "channels" => [
                "pstn" => [
                    [
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ],
                "sip" => [
                    [
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ],
                "vbc" => [
                    [
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ],
                "websocket" => [
                    [
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ],
                "sms" => [
                    [
                        "number" => "447700900000",
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ],
                "mms" => [
                    [
                        "number" => "447700900000",
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ],
                "whatsapp" => [
                    [
                        "number" => "447700900000",
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ],
                "viber" => [
                    [
                        "number" => "447700900000",
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ],
                "messenger" => [
                    [
                        "id" => "0",
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ]
            ]
        ];

        $user->fromArray($userData);

        $response = $this->usersClient->createUser($user);
        $this->assertInstanceOf(User::class, $response);
        $this->assertEquals('my_user_name', $user->getName());
    }

    public function testWillGetUser(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v1/users/USR-82e028d9-5201-4f1e-8188-604b2d3471ec',
                $uriString
            );

            $this->assertEquals('GET', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('get-user-success'));

        $response = $this->usersClient->getUser('USR-82e028d9-5201-4f1e-8188-604b2d3471ec');
        $this->assertInstanceOf(User::class, $response);
        $this->assertEquals('USR-82e028d9-5201-4f1e-8188-604b2d3471ec', $response->getId());
    }

    public function testWillUpdateUser(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v1/users/USR-82e028d9-5201-4f1e-8188-604b2d3471ec',
                $uriString
            );

            $this->assertEquals('PATCH', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('update-user-success'));

        $data  = [
            "id" => "USR-82e028d9-5201-4f1e-8188-604b2d3471ec",
            "name" => "my_patched_user_name",
            "display_name" => "My Patched User Name",
            "image_url" => "https://example.com/image.png",
            "channels" => [
                "pstn" => [
                    [
                        "property1" => "string",
                        "property2" => "string"
                    ]
                ],
            ],
        ];

        $user = new User();
        $user->fromArray($data);

        $response = $this->usersClient->updateUser($user, 'USR-82e028d9-5201-4f1e-8188-604b2d3471ec');
        $this->assertInstanceOf(User::class, $response);
        $this->assertEquals('my_patched_user_name', $user->getName());
        $this->assertEquals('My Patched User Name', $user->getDisplayName());
    }

    public function testWillDeleteUser(): void
    {
        $this->vonageClient->send(Argument::that(function (Request $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/v1/users/USR-82e028d9-5201-4f1e-8188-604b2d3471ec',
                $uriString
            );

            $this->assertEquals('DELETE', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('delete-user-success', 204));

        $user = 'USR-82e028d9-5201-4f1e-8188-604b2d3471ec';

        $response = $this->usersClient->deleteUser($user);
        $this->assertTrue($response);
    }

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'), $status);
    }
}
