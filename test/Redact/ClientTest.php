<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Redact;

use Prophecy\Argument;
use Nexmo\Redact\Client;
use Nexmo\Client\Exception;
use Zend\Diactoros\Response;
use Nexmo\Client\APIResource;
use PHPUnit\Framework\TestCase;
use NexmoTest\Psr7AssertionTrait;
use Nexmo\Client\APIExceptionHandler;
use Psr\Http\Message\RequestInterface;

class ClientTest extends TestCase
{
    use Psr7AssertionTrait;

    /**
     * @var APIResource
     */
    protected $apiClient;

    protected $nexmoClient;

    /**
     * @var Client
     */
    protected $redact;

    public function setUp()
    {
        $this->nexmoClient = $this->prophesize('Nexmo\Client');
        $this->nexmoClient->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->apiClient = new APIResource();
        $this->apiClient->setBaseUri('/v1/redact/transaction')
            ->setCollectionName('')
            ->setClient($this->nexmoClient->reveal())
        ;
        $exceptionHandler = $this->apiClient->getExceptionErrorHandler();
        if ($exceptionHandler instanceof APIExceptionHandler) {
            $exceptionHandler->setRfc7807Format("%s - %s. See %s for more information");
        }
        $this->apiClient->setExceptionErrorHandler($exceptionHandler);

        $this->redact = new Client($this->apiClient);
    }

    public function testUrlAndMethod()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertEquals('/v1/redact/transaction', $request->getUri()->getPath());
            $this->assertEquals('api.nexmo.com', $request->getUri()->getHost());
            $this->assertEquals('POST', $request->getMethod());
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms');
    }
    
    public function testNoOptions()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestJsonBodyContains('id', 'ABC123', $request);
            $this->assertRequestJsonBodyContains('product', 'sms', $request);

            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms');
    }

    public function testWithOptions()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestJsonBodyContains('id', 'ABC123', $request);
            $this->assertRequestJsonBodyContains('product', 'sms', $request);
            $this->assertRequestJsonBodyContains('type', 'inbound', $request);
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms', ['type' => 'inbound']);
    }

    public function testOptionsDoNotOverwriteParams()
    {
        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestJsonBodyContains('id', 'ABC123', $request);
            $this->assertRequestJsonBodyContains('product', 'sms', $request);
            $this->assertRequestJsonBodyContains('type', 'inbound', $request);
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse('success', 204));

        $this->redact->transaction('ABC123', 'sms', ['id' => 'ZZZ', 'type' => 'inbound']);
    }

    /**
     * @dataProvider exceptionsProvider
     */
    public function testExceptions($response, $code, $expectedException, $expectedMessage)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $this->nexmoClient->send(Argument::that(function (RequestInterface $request) {
            return true;
        }))->shouldBeCalledTimes(1)->willReturn($this->getResponse($response, $code));

        $this->redact->transaction('ABC123', 'sms');
    }

    public function exceptionsProvider()
    {
        return [
            'unauthorized' => ['unauthorized', 401, Exception\Request::class, "Unauthorized"],
            'premature-redaction' => ['premature-redaction', 403, Exception\Request::class, "Premature Redaction - You must wait 60 minutes before redacting ID '0A000000B0C9A1234'. See https://developer.nexmo.com/api-errors/redact#premature-redaction"],
            'unprovisioned' => ['unprovisioned', 403, Exception\Request::class, "Authorisation error - User=ABC123 is not provisioned to redact product=SMS. See https://developer.nexmo.com/api-errors#unprovisioned"],
            'invalid-id' => ['invalid-id', 404, Exception\Request::class, "Invalid ID - ID '0A000000B0C9A1234' could not be found (type=MT). See https://developer.nexmo.com/api-errors#invalid-id"],
            'invalid-json' => ['invalid-json', 422, Exception\Request::class, "Invalid JSON - Unexpected character ('\"' (code 34)): was expecting comma to separate Object entries. See https://developer.nexmo.com/api-errors#invalid-json"],
            'unsupported-product' => ['unsupported-product', 422, Exception\Request::class, "Invalid Product - No product corresponding to supplied string sms2!. See https://developer.nexmo.com/api-errors/redact#invalid-product"],
            'unknown-error' => ['error', 500, Exception\Server::class, "Unexpected error"],
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
