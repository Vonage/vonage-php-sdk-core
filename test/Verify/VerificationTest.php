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
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Exception\Request as RequestException;
use Vonage\Client\Exception\Server as ServerException;
use Vonage\Verify\Check;
use Vonage\Verify\Client as VerifyClient;
use Vonage\Verify\Verification;

class VerificationTest extends TestCase
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
        $this->verification = @new Verification($this->number, $this->brand);
        $this->existing = new Verification('44a5279b27dd4a638d614d265ad57a77');
    }

    public function testExistingAndNew(): void
    {
        self::assertTrue(@$this->verification->isDirty());
        self::assertFalse(@$this->existing->isDirty());
    }

    public function testConstructDataAsObject(): void
    {
        self::assertEquals($this->number, @$this->verification->getNumber());
    }

    /**
     * @throws ClientException
     */
    public function testConstructDataAsParams(): void
    {
        $params = $this->verification->getRequestData(false);
        self::assertEquals($this->number, @$params['number']);
        self::assertEquals($this->brand, @$params['brand']);
    }

    public function testConstructDataAsArray(): void
    {
        self::assertEquals($this->number, @$this->verification['number']);
        self::assertEquals($this->brand, @$this->verification['brand']);
    }

    /**
     * @dataProvider optionalValues
     * @param $value
     * @param $setter
     * @param $param
     * @param null $normal
     * @throws ClientException
     * @noinspection PhpUnusedParameterInspection
     */
    public function testCanConstructOptionalValues($value, $setter, $param, $normal = null): void
    {
        if (is_null($normal)) {
            $normal = $value;
        }

        $verification = @new Verification('14845552121', 'brand', [
            $param => $normal
        ]);

        $params = $verification->getRequestData(false);

        self::assertEquals($normal, $params[$param]);
        self::assertEquals($normal, @$verification[$param]);
    }

    /**
     * @dataProvider optionalValues
     * @param $value
     * @param $setter
     * @param $param
     * @param null $normal
     * @throws ClientException
     */
    public function testCanSetOptionalValues($value, $setter, $param, $normal = null): void
    {
        if (is_null($normal)) {
            $normal = $value;
        }

        $this->verification->$setter($value);
        $params = @$this->verification->getRequestData(false);

        self::assertEquals($normal, $params[$param]);
        self::assertEquals($normal, @$this->verification[$param]);
    }

    /**
     * @return string[]
     */
    public function optionalValues(): array
    {
        return [
            ['us', 'setCountry', 'country'],
            ['16105551212', 'setSenderId', 'sender_id'],
            ['6', 'setCodeLength', 'code_length'],
            ['en-us', 'setLanguage', 'lg'],
            ['landline', 'setRequireType', 'require_type'],
            ['400', 'setPinExpiry', 'pin_expiry'],
            ['200', 'setWaitTime', 'next_event_wait'],
        ];
    }

    /**
     * Test that the request id can be accessed when a verification is created with it, or when a request is created.
     */
    public function testRequestId(): void
    {
        self::assertEquals('44a5279b27dd4a638d614d265ad57a77', @$this->existing->getRequestId());

        @$this->verification->setResponse($this->getResponse('search'));

        self::assertEquals('44a5279b27dd4a638d614d265ad57a77', @$this->verification->getRequestId());
    }

    /**
     * Verification provides object access to normalized data (dates as DateTime)
     *
     * @throws Exception
     */
    public function testSearchParamsAsObject(): void
    {
        @$this->existing->setResponse($this->getResponse('search'));

        self::assertEquals('6cff3913', @$this->existing->getAccountId());
        self::assertEquals('14845551212', @$this->existing->getNumber());
        self::assertEquals('verify', @$this->existing->getSenderId());
        self::assertEquals(new DateTime("2016-05-15 03:55:05"), @$this->existing->getSubmitted());
        self::assertEquals(null, @$this->existing->getFinalized());
        self::assertEquals(new DateTime("2016-05-15 03:55:05"), @$this->existing->getFirstEvent());
        self::assertEquals(new DateTime("2016-05-15 03:57:12"), @$this->existing->getLastEvent());
        self::assertEquals('0.10000000', @$this->existing->getPrice());
        self::assertEquals('EUR', @$this->existing->getCurrency());
        self::assertEquals(Verification::FAILED, @$this->existing->getStatus());

        @$checks = $this->existing->getChecks();

        self::assertIsArray($checks);
        self::assertCount(3, $checks);

        foreach ($checks as $index => $check) {
            self::assertInstanceOf(Check::class, $check);
        }

        self::assertEquals('123456', $checks[0]->getCode());
        self::assertEquals('1234', $checks[1]->getCode());
        self::assertEquals('1234', $checks[2]->getCode());
        self::assertEquals(new DateTime('2016-05-15 03:58:11'), $checks[0]->getDate());
        self::assertEquals(new DateTime('2016-05-15 03:55:50'), $checks[1]->getDate());
        self::assertEquals(new DateTime('2016-05-15 03:59:18'), $checks[2]->getDate());
        self::assertEquals(Check::INVALID, $checks[0]->getStatus());
        self::assertEquals(Check::INVALID, $checks[1]->getStatus());
        self::assertEquals(Check::INVALID, $checks[2]->getStatus());
        self::assertEquals(null, $checks[0]->getIpAddress());
        self::assertEquals(null, $checks[1]->getIpAddress());
        self::assertEquals('8.8.4.4', $checks[2]->getIpAddress());
    }

    /**
     * Verification provides simple access to raw data when available.
     *
     * @dataProvider dataResponses
     * @param $type
     * @throws Exception
     */
    public function testResponseDataAsArray($type): void
    {
        @$this->existing->setResponse($this->getResponse($type));
        $json = $this->existing->getResponseData();

        foreach ($json as $key => $value) {
            self::assertEquals($value, @$this->existing[$key], "Could not access `$key` as a property.");
        }

        self::markTestIncomplete('Remove deprecated tests');
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
     * @dataProvider getClientProxyMethods
     * @param $method
     * @param $proxy
     * @param null $code
     * @param null $ip
     */
    public function testMethodsProxyClient($method, $proxy, $code = null, $ip = null): void
    {
        /** @var mixed $client */
        $client = $this->prophesize(VerifyClient::class);

        if (!is_null($ip)) {
            $prediction = $client->$proxy($this->existing, $code, $ip);
        } elseif (!is_null($code)) {
            $prediction = $client->$proxy($this->existing, $code, Argument::cetera());
        } else {
            $prediction = $client->$proxy($this->existing);
        }

        $prediction->shouldBeCalled()->willReturn($this->existing);

        @$this->existing->setClient($client->reveal());

        if (!is_null($ip)) {
            @$this->existing->$method($code, $ip);
        } elseif (!is_null($code)) {
            @$this->existing->$method($code);
        } else {
            @$this->existing->$method();
        }

        self::markTestIncomplete('Remove deprecated tests');
    }

    /**
     * @throws ClientException
     * @throws RequestException
     * @throws ClientExceptionInterface
     * @throws ServerException
     */
    public function testCheckReturnsBoolForInvalidCode(): void
    {
        /** @var mixed $client */
        $client = $this->prophesize(VerifyClient::class);
        $client->check($this->existing, '1234', Argument::cetera())->willReturn($this->existing);
        $client->check($this->existing, '4321', Argument::cetera())->willThrow(new RequestException('dummy', 16));

        @$this->existing->setClient($client->reveal());

        @self::assertFalse($this->existing->check('4321'));
        @self::assertTrue($this->existing->check('1234'));

        self::markTestIncomplete('Remove deprecated tests');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testCheckReturnsBoolForTooManyAttempts(): void
    {
        /** @var mixed $client */
        $client = $this->prophesize(VerifyClient::class);
        $client->check($this->existing, '1234', Argument::cetera())->willReturn($this->existing);
        $client->check($this->existing, '4321', Argument::cetera())->willThrow(new RequestException('dummy', 17));

        @$this->existing->setClient($client->reveal());

        @self::assertFalse($this->existing->check('4321'));
        @self::assertTrue($this->existing->check('1234'));

        self::markTestIncomplete('Remove deprecated tests');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws ClientException
     * @throws RequestException
     * @throws ServerException
     */
    public function testExceptionForCheckFail(): void
    {
        /** @var mixed $client */
        $client = $this->prophesize(VerifyClient::class);
        $client->check($this->existing, '1234', Argument::cetera())->willReturn($this->existing);
        $client->check($this->existing, '4321', Argument::cetera())->willThrow(new RequestException('dummy', 6));

        @$this->existing->setClient($client->reveal());

        $this->expectException(RequestException::class);
        @$this->existing->check('4321');

        self::markTestIncomplete('Remove deprecated tests');
    }

    /**
     * @dataProvider getSerializeResponses
     * @param $response
     * @throws Exception
     */
    public function testSerialize($response): void
    {
        @$this->existing->setResponse($response);
        @$this->existing->getResponse()->getBody()->rewind();
        @$this->existing->getResponse()->getBody()->getContents();

        $serialized = serialize($this->existing);
        $unserialized = unserialize($serialized, [Verification::class]);

        self::assertInstanceOf(get_class($this->existing), $unserialized);
        self::assertEquals(@$this->existing->getAccountId(), @$unserialized->getAccountId());
        self::assertEquals(@$this->existing->getStatus(), @$unserialized->getStatus());
        self::assertEquals(@$this->existing->getResponseData(), @$unserialized->getResponseData());

        self::markTestIncomplete('Remove deprecated tests');
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
     * @dataProvider getClientProxyMethods
     * @param $method
     * @param $proxy
     * @param null $code
     * @param null $ip
     * @noinspection PhpUnusedParameterInspection
     */
    public function testMissingClientException($method, $proxy, $code = null, $ip = null): void
    {
        $this->expectException('RuntimeException');

        if (!is_null($ip)) {
            @$this->existing->$method($code, $ip);
        } elseif (!is_null($code)) {
            @$this->existing->$method($code);
        } else {
            @$this->existing->$method();
        }
    }

    /**
     * @return string[]
     */
    public function getClientProxyMethods(): array
    {
        return [
            ['cancel', 'cancel'],
            ['trigger', 'trigger'],
            ['sync', 'search'],
            ['check', 'check', '1234'],
            ['check', 'check', '1234', '192.168.1.1'],
        ];
    }

    /**
     * Get the API response we'd expect for a call to the API. Verify API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     *
     * @param string $type
     * @return Response
     */
    protected function getResponse(string $type = 'success'): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
    }
}
