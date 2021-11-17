<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Verify;

use DateTime;
use Exception;
use Laminas\Diactoros\Response;
use Vonage\Verify\Request;
use VonageTest\VonageTestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Verify\Check;
use Vonage\Verify\Client as VerifyClient;
use Vonage\Verify\Verification;

use function fopen;
use function get_class;
use function is_null;
use function serialize;
use function unserialize;

class VerificationTest extends VonageTestCase
{
    /**
     * @var string
     */
    protected $number = '14845551212';

    /**
     * @var string
     */
    protected $brand = 'Vonage-PHP';

    /**
     * @var Verification
     */
    protected $verification;

    /**
     * @var Verification
     */
    protected $existing;

    /**
     * Create a basic verification object
     */
    public function setUp(): void
    {
        $this->verification = new Verification(new Request($this->number, $this->brand));
        $this->existing = new Verification('44a5279b27dd4a638d614d265ad57a77');
    }

    public function testConstructDataAsObject(): void
    {
        $this->assertEquals($this->number, @$this->verification->getNumber());
    }

    /**
     * @throws ClientException
     */
    public function testConstructDataAsParams(): void
    {
        $params = $this->verification->getRequestData(false);
        $this->assertEquals($this->number, @$params['number']);
        $this->assertEquals($this->brand, @$params['brand']);
    }

    public function testConstructDataAsArray(): void
    {
        $this->assertEquals($this->number, @$this->verification['number']);
        $this->assertEquals($this->brand, @$this->verification['brand']);
    }

    /**
     * @dataProvider optionalRequestValues
     *
     * @param $value
     * @param $setter
     * @param $param
     * @param null $normal
     *
     * @throws ClientException
     * @noinspection PhpUnusedParameterInspection
     */
    public function testCanConstructOptionalValues($value, $setter, $param, $normal = null): void
    {
        if (is_null($normal)) {
            $normal = $value;
        }

        $request = new Request('14845552121', 'brand');
        $request->{$setter}($normal);

        $verification = new Verification($request);

        $params = $verification->getRequestData(false);

        $this->assertEquals($normal, $params[$param]);
        $this->assertEquals($normal, @$verification[$param]);
    }

    /**
     * @dataProvider optionalVerificationValues
     *
     * @param $value
     * @param $setter
     * @param $param
     *
     * @throws ClientException
     */
    public function testCanSetOptionalValues($value, $setter, $param): void
    {
        $this->verification->{$setter}($value);

        $params = @$this->verification->getRequestData(false);

        $this->assertEquals($value, $params[$param]);
        $this->assertEquals($value, @$this->verification[$param]);
    }

    /**
     * @return string[]
     */
    public function optionalVerificationValues(): array
    {
        return [
            ['us', 'setCountry', 'country'],
            ['09sdf09sg09', 'setSenderId', 'sender_id'],
            [6, 'setCodeLength', 'code_length'],
            ['en-us', 'setLanguage', 'lg'],
            ['landline', 'setRequireType', 'require_type'],
            [400, 'setPinExpiry', 'pin_expiry'],
            [200, 'setWaitTime', 'next_event_wait'],
            [1, 'setWorkflowId', 'workflow_id'],
        ];
    }

    /**
     * @return string[]
     */
    public function optionalRequestValues(): array
    {
        return [
            ['us', 'setCountry', 'country'],
            ['16105551212', 'setSenderId', 'sender_id'],
            [6, 'setCodeLength', 'code_length'],
            ['en-us', 'setLocale', 'lg'],
            [400, 'setPinExpiry', 'pin_expiry'],
            [200, 'setNextEventWait', 'next_event_wait'],
        ];
    }

    /**
     * Test that the request id can be accessed when a verification is created with it, or when a request is created.
     */
    public function testRequestId(): void
    {
        $this->assertEquals('44a5279b27dd4a638d614d265ad57a77', @$this->existing->getRequestId());

        @$this->verification->setResponse($this->getResponse('search'));

        $this->assertEquals('44a5279b27dd4a638d614d265ad57a77', @$this->verification->getRequestId());
    }

    /**
     * Verification provides object access to normalized data (dates as DateTime)
     *
     * @throws Exception
     */
    public function testSearchParamsAsObject(): void
    {
        @$this->existing->setResponse($this->getResponse('search'));

        $this->assertEquals('6cff3913', @$this->existing->getAccountId());
        $this->assertEquals('14845551212', @$this->existing->getNumber());
        $this->assertEquals('verify', @$this->existing->getSenderId());
        $this->assertEquals(new DateTime("2016-05-15 03:55:05"), @$this->existing->getSubmitted());
        $this->assertEquals(null, @$this->existing->getFinalized());
        $this->assertEquals(new DateTime("2016-05-15 03:55:05"), @$this->existing->getFirstEvent());
        $this->assertEquals(new DateTime("2016-05-15 03:57:12"), @$this->existing->getLastEvent());
        $this->assertEquals('0.10000000', @$this->existing->getPrice());
        $this->assertEquals('EUR', @$this->existing->getCurrency());
        $this->assertEquals(Verification::FAILED, @$this->existing->getStatus());

        @$checks = $this->existing->getChecks();

        $this->assertIsArray($checks);
        $this->assertCount(3, $checks);

        foreach ($checks as $index => $check) {
            $this->assertInstanceOf(Check::class, $check);
        }

        $this->assertEquals('123456', $checks[0]->getCode());
        $this->assertEquals('1234', $checks[1]->getCode());
        $this->assertEquals('1234', $checks[2]->getCode());
        $this->assertEquals(new DateTime('2016-05-15 03:58:11'), $checks[0]->getDate());
        $this->assertEquals(new DateTime('2016-05-15 03:55:50'), $checks[1]->getDate());
        $this->assertEquals(new DateTime('2016-05-15 03:59:18'), $checks[2]->getDate());
        $this->assertEquals(Check::INVALID, $checks[0]->getStatus());
        $this->assertEquals(Check::INVALID, $checks[1]->getStatus());
        $this->assertEquals(Check::INVALID, $checks[2]->getStatus());
        $this->assertEquals(null, $checks[0]->getIpAddress());
        $this->assertEquals(null, $checks[1]->getIpAddress());
        $this->assertEquals('8.8.4.4', $checks[2]->getIpAddress());
    }

    /**
     * Verification provides simple access to raw data when available.
     *
     * @dataProvider dataResponses
     *
     * @param $type
     *
     * @throws Exception
     */
    public function testResponseDataAsArray($type): void
    {
        @$this->existing->setResponse($this->getResponse($type));
        $json = $this->existing->getResponseData();

        foreach ($json as $key => $value) {
            $this->assertEquals($value, @$this->existing[$key], "Could not access `$key` as a property.");
        }
    }

    /**
     * @return string[]
     */
    public function dataResponses(): array
    {
        return [
            ['search'],
            ['start']
        ];
    }

    /**
     * @dataProvider getSerializeResponses
     *
     * @param $response
     *
     * @throws Exception
     */
    public function testSerialize($response): void
    {
        @$this->existing->setResponse($response);
        @$this->existing->getResponse()->getBody()->rewind();
        @$this->existing->getResponse()->getBody()->getContents();

        $serialized = serialize($this->existing);
        $unserialized = unserialize($serialized, [Verification::class]);

        $this->assertInstanceOf(get_class($this->existing), $unserialized);
        $this->assertEquals(@$this->existing->getAccountId(), @$unserialized->getAccountId());
        $this->assertEquals(@$this->existing->getStatus(), @$unserialized->getStatus());
        $this->assertEquals(@$this->existing->getResponseData(), @$unserialized->getResponseData());
    }

    /**
     * @return Response[]
     */
    public function getSerializeResponses(): array
    {
        return [
            [$this->getResponse('search')],
            [$this->getResponse('start')],
        ];
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
