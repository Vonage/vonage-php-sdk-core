<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Verify;

use InvalidArgumentException;
use Laminas\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Verify\Client as VerifyClient;
use Vonage\Verify\Request;
use Vonage\Verify\RequestPSD2;
use Vonage\Verify\Verification;
use VonageTest\Psr7AssertionTrait;

use function array_unshift;
use function call_user_func_array;
use function fopen;
use function serialize;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var VerifyClient
     */
    protected $client;

    protected $vonageClient;

    /**
     * Create the Message API Client, and mock the Vonage Client
     */
    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->client = new VerifyClient();

        /** @noinspection PhpParamsInspection */
        $this->client->setClient($this->vonageClient->reveal());
    }

    /**
     * @dataProvider getApiMethods
     *
     * @param $method
     * @param $response
     * @param $construct
     * @param array $args
     */
    public function testClientSetsSelf($method, $response, $construct, $args = []): void
    {
        /** @var mixed $client */
        $client = $this->prophesize(Client::class);
        $client->send(Argument::cetera())->willReturn($this->getResponse($response));
        $client->getApiUrl()->willReturn('http://api.nexmo.com');

        $this->client->setClient($client->reveal());

        $mock = @$this->getMockBuilder(Verification::class)
            ->setConstructorArgs($construct)
            ->setMethods(['setClient'])
            ->getMock();

        $mock->expects(self::once())->method('setClient')->with($this->client);

        array_unshift($args, $mock);
        @call_user_func_array([$this->client, $method], $args);
    }

    /**
     * @return array[]
     */
    public function getApiMethods(): array
    {
        return [
            ['start', 'start', ['14845551212', 'Test Verify']],
            ['cancel', 'cancel', ['44a5279b27dd4a638d614d265ad57a77']],
            ['trigger', 'trigger', ['44a5279b27dd4a638d614d265ad57a77']],
            ['search', 'search', ['44a5279b27dd4a638d614d265ad57a77']],
            ['check', 'check', ['44a5279b27dd4a638d614d265ad57a77'], ['1234']],
        ];
    }

    public function testUnserializeAcceptsObject(): void
    {
        $mock = @$this->getMockBuilder(Verification::class)
            ->setConstructorArgs(['14845551212', 'Test Verify'])
            ->setMethods(['setClient'])
            ->getMock();

        $mock->expects(self::once())->method('setClient')->with($this->client);

        @$this->client->unserialize($mock);
    }

    /**
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     * @throws ClientExceptionInterface
     */
    public function testUnserializeSetsClient(): void
    {
        $verification = @new Verification('14845551212', 'Test Verify');
        @$verification->setResponse($this->getResponse('start'));

        $string = serialize($verification);
        $object = @$this->client->unserialize($string);

        $this->assertInstanceOf(Verification::class, $object);

        $search = $this->setupClientForSearch('search');
        @$object->sync();

        $this->assertSame($search, @$object->getResponse());
    }

    public function testSerializeMatchesEntity(): void
    {
        $verification = @new Verification('14845551212', 'Test Verify');
        @$verification->setResponse($this->getResponse('start'));

        $string = serialize($verification);

        $this->assertSame($string, @$this->client->serialize($verification));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     *
     * @deprecated
     */
    public function testCanStartVerificationWithVerificationObject(): void
    {
        $success = $this->setupClientForStart('start');

        $verification = @new Verification('14845551212', 'Test Verify');
        @$this->client->start($verification);

        $this->assertSame($success, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanStartVerification(): void
    {
        $success = $this->setupClientForStart('start');

        $verification = new Request('14845551212', 'Test Verify');
        $verification = @$this->client->start($verification);

        $this->assertSame($success, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanStartPSD2Verification(): void
    {
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) {
                    $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                    $this->assertRequestJsonBodyContains('payee', 'Test Verify', $request);
                    $this->assertRequestJsonBodyContains('amount', '5.25', $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/psd2/json', $request);

                    return true;
                }
            )
        )->willReturn($this->getResponse('start'))
            ->shouldBeCalledTimes(1);

        $request = new RequestPSD2('14845551212', 'Test Verify', '5.25');
        $response = @$this->client->requestPSD2($request);

        $this->assertSame('0', $response['status']);
        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $response['request_id']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanStartPSD2VerificationWithWorkflowID(): void
    {
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) {
                    $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                    $this->assertRequestJsonBodyContains('payee', 'Test Verify', $request);
                    $this->assertRequestJsonBodyContains('amount', '5.25', $request);
                    $this->assertRequestJsonBodyContains('workflow_id', 5, $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/psd2/json', $request);

                    return true;
                }
            )
        )->willReturn($this->getResponse('start'))
            ->shouldBeCalledTimes(1);

        $request = new RequestPSD2('14845551212', 'Test Verify', '5.25', 5);
        $response = @$this->client->requestPSD2($request);

        $this->assertSame('0', $response['status']);
        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $response['request_id']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanStartArray(): void
    {
        $response = $this->setupClientForStart('start');

        @$verification = $this->client->start(
            [
                'number' => '14845551212',
                'brand' => 'Test Verify'
            ]
        );

        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws ServerException
     */
    public function testStartThrowsException(): void
    {
        $response = $this->setupClientForStart('start-error');

        try {
            @$this->client->start(
                [
                    'number' => '14845551212',
                    'brand' => 'Test Verify'
                ]
            );

            self::fail('did not throw exception');
        } catch (Client\Exception\Request $e) {
            $this->assertEquals('2', $e->getCode());
            $this->assertEquals(
                'Your request is incomplete and missing the mandatory parameter: brand',
                $e->getMessage()
            );
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     */
    public function testStartThrowsServerException(): void
    {
        $response = $this->setupClientForStart('server-error');

        try {
            @$this->client->start(
                [
                    'number' => '14845551212',
                    'brand' => 'Test Verify'
                ]
            );

            self::fail('did not throw exception');
        } catch (ServerException $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @param $response
     */
    protected function setupClientForStart($response): Response
    {
        $response = $this->getResponse($response);
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) {
                    $this->assertRequestJsonBodyContains('number', '14845551212', $request);
                    $this->assertRequestJsonBodyContains('brand', 'Test Verify', $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/json', $request);

                    return true;
                }
            )
        )->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanSearchVerification(): void
    {
        $response = $this->setupClientForSearch('search');

        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        @$this->client->search($verification);

        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanSearchId(): void
    {
        $response = $this->setupClientForSearch('search');

        $verification = @$this->client->search('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws ServerException
     */
    public function testSearchThrowsException(): void
    {
        $response = $this->setupClientForSearch('search-error');

        try {
            @$this->client->search('44a5279b27dd4a638d614d265ad57a77');

            self::fail('did not throw exception');
        } catch (Client\Exception\Request $e) {
            $this->assertEquals('101', $e->getCode());
            $this->assertEquals('No response found', $e->getMessage());
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     */
    public function testSearchThrowsServerException(): void
    {
        $response = $this->setupClientForSearch('server-error');

        try {
            @$this->client->search('44a5279b27dd4a638d614d265ad57a77');

            self::fail('did not throw exception');
        } catch (ServerException $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testSearchReplacesResponse(): void
    {
        $old = $this->getResponse('start');
        $verification = @new Verification('14845551212', 'Test Verify');
        @$verification->setResponse($old);

        $response = $this->setupClientForSearch('search');
        @$this->client->search($verification);

        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @param $response
     */
    protected function setupClientForSearch($response): Response
    {
        $response = $this->getResponse($response);
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) {
                    $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/search/json', $request);

                    return true;
                }
            )
        )->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanCancelVerification(): void
    {
        $response = $this->setupClientForControl('cancel', 'cancel');

        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        $result = @$this->client->cancel($verification);

        $this->assertSame($verification, $result);
        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanCancelId(): void
    {
        $response = $this->setupClientForControl('cancel', 'cancel');

        $verification = @$this->client->cancel('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws ServerException
     */
    public function testCancelThrowsClientException(): void
    {
        $response = $this->setupClientForControl('cancel-error', 'cancel');

        try {
            @$this->client->cancel('44a5279b27dd4a638d614d265ad57a77');

            self::fail('did not throw exception');
        } catch (Client\Exception\Request $e) {
            $this->assertEquals('19', $e->getCode());
            $this->assertEquals(
                "Verification request  ['c1878c7451f94c1992d52797df57658e'] can't " .
                "be cancelled now. Too many attempts to re-deliver have already been made.",
                $e->getMessage()
            );
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     */
    public function testCancelThrowsServerException(): void
    {
        $response = $this->setupClientForControl('server-error', 'cancel');

        try {
            @$this->client->cancel('44a5279b27dd4a638d614d265ad57a77');

            self::fail('did not throw exception');
        } catch (ServerException $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanTriggerId(): void
    {
        $response = $this->setupClientForControl('trigger', 'trigger_next_event');

        $verification = @$this->client->trigger('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanTriggerVerification(): void
    {
        $response = $this->setupClientForControl('trigger', 'trigger_next_event');

        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        $result = @$this->client->trigger($verification);

        $this->assertSame($verification, $result);
        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws ServerException
     */
    public function testTriggerThrowsClientException(): void
    {
        $response = $this->setupClientForControl('trigger-error', 'trigger_next_event');

        try {
            @$this->client->trigger('44a5279b27dd4a638d614d265ad57a77');

            self::fail('did not throw exception');
        } catch (Client\Exception\Request $e) {
            $this->assertEquals('6', $e->getCode());
            $this->assertEquals(
                "The requestId '44a5279b27dd4a638d614d265ad57a77' does " .
                "not exist or its no longer active.",
                $e->getMessage()
            );
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     */
    public function testTriggerThrowsServerException(): void
    {
        $response = $this->setupClientForControl('server-error', 'trigger_next_event');

        try {
            @$this->client->trigger('44a5279b27dd4a638d614d265ad57a77');

            self::fail('did not throw exception');
        } catch (ServerException $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @dataProvider getControlCommands
     *
     * @param $method
     * @param $cmd
     */
    public function testControlNotReplaceResponse($method, $cmd): void
    {
        $response = $this->getResponse('search');
        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        @$verification->setResponse($response);

        $this->setupClientForControl($method, $cmd);
        @$this->client->$method($verification);

        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @return string[]
     */
    public function getControlCommands(): array
    {
        return [
            ['cancel', 'cancel'],
            ['trigger', 'trigger_next_event']
        ];
    }

    /**
     * @param $response
     * @param $cmd
     */
    protected function setupClientForControl($response, $cmd): Response
    {
        $response = $this->getResponse($response);
        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) use ($cmd) {
                    $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                    $this->assertRequestJsonBodyContains('cmd', $cmd, $request);
                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/control/json', $request);

                    return true;
                }
            )
        )->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanCheckVerification(): void
    {
        $response = $this->setupClientForCheck('check', '1234');
        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');

        @$this->client->check($verification, '1234');

        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCanCheckId(): void
    {
        $response = $this->setupClientForCheck('check', '1234');
        $verification = @$this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

        $this->assertSame($response, @$verification->getResponse());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws ServerException
     */
    public function testCheckThrowsClientException(): void
    {
        $response = $this->setupClientForCheck('check-error', '1234');

        try {
            @$this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

            self::fail('did not throw exception');
        } catch (Client\Exception\Request $e) {
            $this->assertEquals('16', $e->getCode());
            $this->assertEquals('The code provided does not match the expected value', $e->getMessage());
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     */
    public function testCheckThrowsServerException(): void
    {
        $response = $this->setupClientForCheck('server-error', '1234');

        try {
            @$this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

            self::fail('did not throw exception');
        } catch (ServerException $e) {
            $this->assertEquals('5', $e->getCode());
            $this->assertEquals('Server Error', $e->getMessage());
            $this->assertSame($response, @$e->getEntity()->getResponse());
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws Client\Exception\Exception
     * @throws Client\Exception\Request
     * @throws ServerException
     */
    public function testCheckNotReplaceResponse(): void
    {
        $old = $this->getResponse('search');
        $verification = new Verification('44a5279b27dd4a638d614d265ad57a77');
        @$verification->setResponse($old);

        $this->setupClientForCheck('check', '1234');

        @$this->client->check($verification, '1234');
        $this->assertSame($old, @$verification->getResponse());
    }

    /**
     * @param $response
     * @param $code
     */
    protected function setupClientForCheck($response, $code, ?string $ip = null): Response
    {
        $response = $this->getResponse($response);

        $this->vonageClient->send(
            Argument::that(
                function (RequestInterface $request) use ($code, $ip) {
                    $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
                    $this->assertRequestJsonBodyContains('code', $code, $request);

                    if ($ip) {
                        $this->assertRequestJsonBodyContains('ip_address', $ip, $request);
                    }

                    $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/check/json', $request);
                    return true;
                }
            )
        )->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    /**
     * Get the API response we'd expect for a call to the API. Verify API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
    protected function getResponse(string $type = 'success'): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
    }
}
