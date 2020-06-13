<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Verify;

use Nexmo\Verify\Check;
use Nexmo\Verify\Request;
use Nexmo\Verify\Verification;
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
    protected $brand  = 'Nexmo-PHP';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Verification
     */
    protected $existing;

    /**
     * Create a basic verification object
     */
    public function setUp()
    {
        $this->request = new Request($this->number, $this->brand);

        $rawData = json_decode(file_get_contents(__DIR__ . '/responses/search.json'), true);
        $this->existing = new Verification($rawData);
    }

    public function testConstructData()
    {
        $this->assertEquals($this->number, $this->request->getNumber());
        $this->assertEquals($this->brand, $this->request->getBrand());
    }

    /**
     * Verification provides object access to normalized data (dates as DateTime)
     */
    public function testSearchParamsAsObject()
    {
        $this->assertEquals('6cff3913', $this->existing->getAccountId());
        $this->assertEquals('14845551212', $this->existing->getNumber());
        $this->assertEquals('verify', $this->existing->getSenderId());
        $this->assertEquals(new \DateTime("2016-05-15 03:55:05"), $this->existing->getSubmitted());
        $this->assertEquals(null, $this->existing->getFinalized());
        $this->assertEquals(new \DateTime("2016-05-15 03:55:05"), $this->existing->getFirstEvent());
        $this->assertEquals(new \DateTime("2016-05-15 03:57:12"), $this->existing->getLastEvent());
        $this->assertEquals('0.10000000', $this->existing->getPrice());
        $this->assertEquals('EUR', $this->existing->getCurrency());
        $this->assertEquals(Verification::FAILED, $this->existing->getStatus());

        $checks = $this->existing->getChecks();
        $this->assertInternalType('array', $checks);
        $this->assertCount(3, $checks);

        foreach ($checks as $index => $check) {
            $this->assertInstanceOf('Nexmo\Verify\Check', $check);
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

    public function testSerialize()
    {
        $serialized   = serialize($this->existing);
        /* @var $unserialized Verification */
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(get_class($this->existing), $unserialized);

        $this->assertEquals($this->existing->getAccountId(), $unserialized->getAccountId());
        $this->assertEquals($this->existing->getStatus(), $unserialized->getStatus());
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
