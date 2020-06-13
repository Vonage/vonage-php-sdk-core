<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Verify;

use Exception;
use Prophecy\Argument;
use Nexmo\Verify\Client;
use Nexmo\Verify\Request;
use Zend\Diactoros\Response;
use Nexmo\Client\APIResource;
use Nexmo\Client\Exception\Request as ExceptionRequest;
use Nexmo\Client\Exception\Server;
use Nexmo\Verify\ExceptionErrorHandler;
use Nexmo\Verify\Verification;
use PHPUnit\Framework\TestCase;
use NexmoTest\Psr7AssertionTrait;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var Client
     */
    protected $client;

    protected $nexmoClient;

    /**
     * @var APIResource
     */
    protected $apiResource;

    /**
     * Create the Message API Client, and mock the Nexmo Client
     */
    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->apiResource = new APIResource();
        $this->apiResource->setClient($this->nexmoClient->reveal())
            ->setIsHAL(false)
            ->setBaseUri('/verify')
            ->setErrorsOn200(true)
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
        ;

        $this->client = new Client($this->apiResource);
    }

    public function testCanStartVerification()
    {
        $this->setupClientForStart('start');

        $verification = new Request('14845551212', 'Test Verify');
        $response = $this->client->start($verification);
        $this->assertSame(0, $response->getStatus());
        $this->assertSame('44a5279b27dd4a638d614d265ad57a77', $response->getRequestId());
    }

    public function testStartThrowsServerException()
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('Server Error');
        $this->expectExceptionCode('5');

        $this->setupClientForStart('server-error');
        $verification = new Request('14845551212', 'Test Verify');
        $this->client->start($verification);
    }

    protected function setupClientForStart($response)
    {
        $response = $this->getResponse($response);
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestJsonBodyContains('number', '14845551212', $request);
            $this->assertRequestJsonBodyContains('brand', 'Test Verify', $request);
            $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/json', $request);
            return true;
        }))->willReturn($response)
           ->shouldBeCalledTimes(1);

        return $response;
    }

    public function testCanSearchVerification()
    {
        $response = $this->setupClientForSearch('search');
        $expected = json_decode($response->getBody()->getContents(), true);
        $response->getBody()->rewind();

        $verification = $this->client->search('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame($expected['request_id'], $verification->getRequestId());
        $this->assertSame($expected['account_id'], $verification->getAccountId());
        $this->assertSame($expected['status'], $verification->getStatus());
    }

    public function testSearchThrowsException()
    {
        $this->expectException(ExceptionRequest::class);
        $this->expectExceptionMessage("No response found");
        $this->expectExceptionCode('101');

        $this->setupClientForSearch('search-error');
        $this->client->search('44a5279b27dd4a638d614d265ad57a77');
    }

    public function testSearchThrowsServerException()
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('Server Error');
        $this->expectExceptionCode('5');

        $this->setupClientForSearch('server-error');

        $this->client->search('44a5279b27dd4a638d614d265ad57a77');
    }

    protected function setupClientForSearch($response)
    {
        $response = $this->getResponse($response);
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
            $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/search/json', $request);
            return true;
        }))->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    public function testCanCancelVerification()
    {
        $this->setupClientForControl('cancel', 'cancel');

        $result = $this->client->cancel('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame(0, $result->getStatus());
        $this->assertSame('cancel', $result->getCommand());
    }

    public function testCancelThrowsClientException()
    {
        $this->expectException(ExceptionRequest::class);
        $this->expectExceptionMessage("Verification request  ['c1878c7451f94c1992d52797df57658e'] can't be cancelled now. Too many attempts to re-deliver have already been made.");
        $this->expectExceptionCode('19');

        $this->setupClientForControl('cancel-error', 'cancel');
        $this->client->cancel('44a5279b27dd4a638d614d265ad57a77');
    }

    public function testCancelThrowsServerException()
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('Server Error');
        $this->expectExceptionCode('5');

        $this->setupClientForControl('server-error', 'cancel');
        $this->client->cancel('44a5279b27dd4a638d614d265ad57a77');
    }

    public function testCanTriggerVerification()
    {
        $this->setupClientForControl('trigger', 'trigger_next_event');

        $result = $this->client->trigger('44a5279b27dd4a638d614d265ad57a77');

        $this->assertSame(0, $result->getStatus());
        $this->assertSame('trigger_next_event', $result->getCommand());
    }

    public function testTriggerThrowsClientException()
    {
        $this->expectException(ExceptionRequest::class);
        $this->expectExceptionMessage("The requestId '44a5279b27dd4a638d614d265ad57a77' does not exist or its no longer active.");
        $this->expectExceptionCode('6');

        $this->setupClientForControl('trigger-error', 'trigger_next_event');
        $this->client->trigger('44a5279b27dd4a638d614d265ad57a77');
    }

    public function testTriggerThrowsServerException()
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('Server Error');
        $this->expectExceptionCode('5');

        $this->setupClientForControl('server-error', 'trigger_next_event');
        $this->client->trigger('44a5279b27dd4a638d614d265ad57a77');
    }
    
    protected function setupClientForControl($response, $cmd)
    {
        $response = $this->getResponse($response);
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($cmd) {
            $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
            $this->assertRequestJsonBodyContains('cmd', $cmd, $request);
            $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/control/json', $request);
            return true;
        }))->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    public function testCanCheckVerification()
    {
        $responseBody = $this->setupClientForCheck('check', '1234')->getBody();
        $expected = json_decode($responseBody->getContents(), true);
        $responseBody->rewind();
        $check = $this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');

        $this->assertSame($expected['request_id'], $check->getRequestId());
        $this->assertSame((int) $expected['status'], $check->getStatus());
        $this->assertSame($expected['price'], $check->getPrice());
        $this->assertSame($expected['currency'], $check->getCurrency());
    }

    public function testCheckThrowsClientException()
    {
        $this->expectException(ExceptionRequest::class);
        $this->expectExceptionMessage('The code provided does not match the expected value');
        $this->expectExceptionCode('16');

        $this->setupClientForCheck('check-error', '1234');

        $this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');
    }

    public function testCheckThrowsServerException()
    {
        $this->expectException(Server::class);
        $this->expectExceptionMessage('Server Error');
        $this->expectExceptionCode('5');

        $this->setupClientForCheck('server-error', '1234');

        $this->client->check('44a5279b27dd4a638d614d265ad57a77', '1234');
    }

    protected function setupClientForCheck($response, $code, $ip = null)
    {
        $response = $this->getResponse($response);
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) use ($code, $ip) {
            $this->assertRequestJsonBodyContains('request_id', '44a5279b27dd4a638d614d265ad57a77', $request);
            $this->assertRequestJsonBodyContains('code', $code, $request);

            if ($ip) {
                $this->assertRequestJsonBodyContains('ip_address', $ip, $request);
            }

            $this->assertRequestMatchesUrl('https://api.nexmo.com/verify/check/json', $request);
            return true;
        }))->willReturn($response)
            ->shouldBeCalledTimes(1);

        return $response;
    }

    /**
     * Get the API response we'd expect for a call to the API. Verify API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse($type = 'success')
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'));
    }
}
