<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Verify;

use Vonage\Verify\Check;
use Vonage\Verify\Verification;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;
use Prophecy\Argument;
use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;

class VerificationTest extends TestCase
{
    /**
     * @var string
     */
    protected $number = '14845551212';

    /**
     * @var string
     */
    protected $brand  = 'Vonage-PHP';

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
        $this->existing    = new Verification('44a5279b27dd4a638d614d265ad57a77');
    }

    public function testexistingAndNew()
    {
        $this->assertTrue(@$this->verification->isDirty());
        $this->assertFalse(@$this->existing->isDirty());
    }

    public function testConstructDataAsObject()
    {
        $this->assertEquals($this->number, @$this->verification->getNumber());
    }

    public function testConstructDataAsParams()
    {
        $params = $this->verification->getRequestData(false);
        $this->assertEquals($this->number, @$params['number']);
        $this->assertEquals($this->brand, @$params['brand']);
    }

    public function testConstructDataAsArray()
    {
        $this->assertEquals($this->number, @$this->verification['number']);
        $this->assertEquals($this->brand, @$this->verification['brand']);
    }

    /**
     * @dataProvider optionalValues
     */
    public function testCanConstructOptionalValues($value, $setter, $param, $normal = null)
    {
        if (is_null($normal)) {
            $normal = $value;
        }

        $verification = @new Verification('14845552121', 'brand', [
            $param => $normal
        ]);

        $params = $verification->getRequestData(false);
        $this->assertEquals($normal, $params[$param]);
        $this->assertEquals($normal, @$verification[$param]);
    }

    /**
     * @dataProvider optionalValues
     */
    public function testCanSetOptionalValues($value, $setter, $param, $normal = null)
    {
        if (is_null($normal)) {
            $normal = $value;
        }

        $this->verification->$setter($value);
        $params = @$this->verification->getRequestData(false);
        $this->assertEquals($normal, $params[$param]);
        $this->assertEquals($normal, @$this->verification[$param]);
    }

    public function optionalValues()
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
    public function testRequestId()
    {
        $this->assertEquals('44a5279b27dd4a638d614d265ad57a77', @$this->existing->getRequestId());

        @$this->verification->setResponse($this->getResponse('search'));
        $this->assertEquals('44a5279b27dd4a638d614d265ad57a77', @$this->verification->getRequestId());
    }

    /**
     * Verification provides object access to normalized data (dates as DateTime)
     */
    public function testSearchParamsAsObject()
    {
        @$this->existing->setResponse($this->getResponse('search'));

        $this->assertEquals('6cff3913', @$this->existing->getAccountId());
        $this->assertEquals('14845551212', @$this->existing->getNumber());
        $this->assertEquals('verify', @$this->existing->getSenderId());
        $this->assertEquals(new \DateTime("2016-05-15 03:55:05"), @$this->existing->getSubmitted());
        $this->assertEquals(null, @$this->existing->getFinalized());
        $this->assertEquals(new \DateTime("2016-05-15 03:55:05"), @$this->existing->getFirstEvent());
        $this->assertEquals(new \DateTime("2016-05-15 03:57:12"), @$this->existing->getLastEvent());
        $this->assertEquals('0.10000000', @$this->existing->getPrice());
        $this->assertEquals('EUR', @$this->existing->getCurrency());
        $this->assertEquals(Verification::FAILED, @$this->existing->getStatus());

        @$checks = $this->existing->getChecks();
        $this->assertInternalType('array', $checks);
        $this->assertCount(3, $checks);

        foreach ($checks as $index => $check) {
            $this->assertInstanceOf('Vonage\Verify\Check', $check);
        }

        $this->assertEquals('123456', $checks[0]->getCode());
        $this->assertEquals('1234', $checks[1]->getCode());
        $this->assertEquals('1234', $checks[2]->getCode());

        $this->assertEquals(new \DateTime('2016-05-15 03:58:11'), $checks[0]->getDate());
        $this->assertEquals(new \DateTime('2016-05-15 03:55:50'), $checks[1]->getDate());
        $this->assertEquals(new \DateTime('2016-05-15 03:59:18'), $checks[2]->getDate());

        $this->assertEquals(Check::INVALID, $checks[0]->getStatus());
        $this->assertEquals(Check::INVALID, $checks[1]->getStatus());
        $this->assertEquals(Check::INVALID, $checks[2]->getStatus());

        $this->assertEquals(null, $checks[0]->getIpAddress());
        $this->assertEquals(null, $checks[1]->getIpAddress());
        $this->assertEquals('8.8.4.4', $checks[2]->getIpAddress());
    }

    /**
     * Verification provides simple access to raw data when available.
     * @todo Removed deprecated tests
     * 
     * @dataProvider dataResponses
     */
    public function testResponseDataAsArray($type)
    {
        @$this->existing->setResponse($this->getResponse($type));
        $json = $this->existing->getResponseData();

        foreach ($json as $key => $value) {
            $this->assertEquals($value, @$this->existing[$key], "Could not access `$key` as a property.");
        }
    }

    public function dataResponses()
    {
        return [
            ['search'],
            ['start']
        ];
    }

    /**
     * @todo Removed deprecated tests
     * @dataProvider getClientProxyMethods
     */
    public function testMethodsProxyClient($method, $proxy, $code = null, $ip = null)
    {
        $client = $this->prophesize('Vonage\Verify\Client');
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
    }

    /**
     * @todo Remove deprecated tests
     */
    public function testCheckReturnsBoolForInvalidCode()
    {
        $client = $this->prophesize('Vonage\Verify\Client');
        $client->check($this->existing, '1234', Argument::cetera())->willReturn($this->existing);
        $client->check($this->existing, '4321', Argument::cetera())->willThrow(new \Vonage\Client\Exception\Request('dummy', '16'));

        @$this->existing->setClient($client->reveal());

        @$this->assertFalse($this->existing->check('4321'));
        @$this->assertTrue($this->existing->check('1234'));
    }

    /**
     * @todo Remove deprecated tests
     */
    public function testCheckReturnsBoolForTooManyAttempts()
    {
        $client = $this->prophesize('Vonage\Verify\Client');
        $client->check($this->existing, '1234', Argument::cetera())->willReturn($this->existing);
        $client->check($this->existing, '4321', Argument::cetera())->willThrow(new \Vonage\Client\Exception\Request('dummy', '17'));

        @$this->existing->setClient($client->reveal());

        @$this->assertFalse($this->existing->check('4321'));
        @$this->assertTrue($this->existing->check('1234'));
    }

    /**
     * @todo Remove deprecated tests
     */
    public function testExceptionForCheckFail()
    {
        $client = $this->prophesize('Vonage\Verify\Client');
        $client->check($this->existing, '1234', Argument::cetera())->willReturn($this->existing);
        $client->check($this->existing, '4321', Argument::cetera())->willThrow(new \Vonage\Client\Exception\Request('dummy', '6'));

        @$this->existing->setClient($client->reveal());

        $this->expectException('Vonage\Client\Exception\Request');
        @$this->existing->check('4321');
    }

    /**
     * @todo Remove deprecated tests
     * @dataProvider getSerializeResponses
     */
    public function testSerialize($response)
    {
        @$this->existing->setResponse($response);
        @$this->existing->getResponse()->getBody()->rewind();
        @$this->existing->getResponse()->getBody()->getContents();
        $serialized   = serialize($this->existing);
        /* @var $unserialized Verification */
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(get_class($this->existing), $unserialized);

        $this->assertEquals(@$this->existing->getAccountId(), @$unserialized->getAccountId());
        $this->assertEquals(@$this->existing->getStatus(), @$unserialized->getStatus());

        $this->assertEquals(@$this->existing->getResponseData(), @$unserialized->getResponseData());
    }

    public function getSerializeResponses()
    {
        return [
            [$this->getResponse('search')],
            [$this->getResponse('start')],
        ];
    }

    /**
     * @dataProvider getClientProxyMethods
     */
    public function testMissingClientException($method, $proxy, $code = null, $ip = null)
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

    public function getClientProxyMethods()
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
    protected function getResponse($type)
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'r'));
    }
}
