<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Network\Number;


class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testNullValuesNotPresent()
    {
        $request = new Request('14443332121', 'http://example.com');
        $params = $request->getParams();

        $this->assertCount(2, $params);
        $this->assertArrayHasKey('number', $params);
        $this->assertArrayHasKey('callback', $params);
    }

    public function testNumberMatchesParams()
    {
        $request = new Request('14443332121', 'http://example.com');
        $params = $request->getParams();
        $this->assertArrayHasKey('number', $params);
        $this->assertEquals('14443332121', $params['number']);
    }

    public function testCallbackMatchesParams()
    {
        $request = new Request('14443332121', 'http://example.com');
        $params = $request->getParams();
        $this->assertArrayHasKey('callback', $params);
        $this->assertEquals('http://example.com', $params['callback']);
    }

    public function testFeaturesMatchesParams()
    {
        $request = new Request('14443332121', 'http://example.com', array(Request::FEATURE_CARRIER, Request::FEATURE_PORTED));
        $params = $request->getParams();
        $this->assertArrayHasKey('features', $params);
        $this->assertInternalType('string', $params['features']);

        $array = explode(',', $params['features']);
        $this->assertCount(2, $array);
        $this->assertContains(Request::FEATURE_CARRIER, $array);
        $this->assertContains(Request::FEATURE_PORTED, $array);
    }

    public function testCallbackTimeoutMatchesParams()
    {
        $request = new Request('14443332121', 'http://example.com', array(), 100);
        $params = $request->getParams();
        $this->assertArrayHasKey('callback_timeout', $params);
        $this->assertEquals(100, $params['callback_timeout']);
    }

    public function testCallbackMethodMatchesParams()
    {
        $request = new Request('14443332121', 'http://example.com', array(), null, 'POST');
        $params = $request->getParams();
        $this->assertArrayHasKey('callback_method', $params);
        $this->assertEquals('POST', $params['callback_method']);
    }

    public function testRefMatchesParams()
    {
        $request = new Request('14443332121', 'http://example.com', array(), null, null, 'ref');
        $params = $request->getParams();
        $this->assertArrayHasKey('client_ref', $params);
        $this->assertEquals('ref', $params['client_ref']);
    }
}
 