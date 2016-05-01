<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify;


class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected $number = '14841115454';
    protected $brand  = 'Super Amazing App';

    public function testOptionalValues()
    {
        $request = new Request($this->number, $this->brand);
        $params = $request->getParams();

        $this->assertArrayNotHasKey('sender_id', $params);
        $this->assertArrayNotHasKey('code_length', $params);
        $this->assertArrayNotHasKey('lg', $params);
    }

    public function testRequiredValues()
    {
        $request = new Request($this->number, $this->brand);
        $params = $request->getParams();

        $this->assertArrayHasKey('number', $params);
        $this->assertArrayHasKey('brand', $params);

        $this->assertEquals($this->number, $params['number']);
        $this->assertEquals($this->brand,  $params['brand']);
    }

    public function testSender()
    {
        $request = new Request($this->number, $this->brand, 'SENDER');
        $params = $request->getParams();

        $this->assertArrayHasKey('sender_id', $params);
        $this->assertEquals('SENDER',  $params['sender_id']);
    }

    /**
     * @dataProvider getLength
     */
    public function testLength($length, $valid)
    {
        if(!$valid){
            $this->setExpectedException('InvalidArgumentException');
        }

        $request = new Request($this->number, $this->brand, 'SENDER', $length);
        $params = $request->getParams();

        $this->assertArrayHasKey('code_length', $params);
        $this->assertEquals($length,  $params['code_length']);
    }

    public function getLength()
    {
        return array(
            array(4, true),
            array(6, true),
            array(5, false)
        );
    }

    public function testLang()
    {
        $request = new Request($this->number, $this->brand, null, null, 'en-us');
        $params = $request->getParams();

        $this->assertArrayHasKey('lg', $params);
        $this->assertEquals('en-us',  $params['lg']);
    }
}
