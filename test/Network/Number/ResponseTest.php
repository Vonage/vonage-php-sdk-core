<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Network\Number;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected $data = array(
        'request_id' => '12345',
        'number' => '14443332121',
        'remaining_balance' => 123.45,
        'request_price' => 0.05,
        'callback_total_parts' => 2,
        'status' => 0,
    );

    /**
     * @var Response;
     */
    protected $response;

    public function setUp()
    {
        $this->response = new Response($this->data);
    }

    public function testMethodsMatchData()
    {
        $this->assertEquals($this->data['request_id'], $this->response->getId());
        $this->assertEquals($this->data['number'], $this->response->getNumber());
        $this->assertEquals($this->data['request_price'], $this->response->getPrice());
        $this->assertEquals($this->data['remaining_balance'], $this->response->getBalance());
        $this->assertEquals($this->data['callback_total_parts'], $this->response->getCallbackTotal());
        $this->assertEquals($this->data['status'], $this->response->getStatus());

    }

    /**
     * @dataProvider getOptionalProperties
     * @param $property
     */
    public function testCantGetOptionalDataBeforeCallback($property)
    {
        $this->setExpectedException('BadMethodCallException');
        $get = 'get' . $property;
        $this->response->$get();
    }

    /**
     * @dataProvider getOptionalProperties
     * @param $property
     */
    public function testCantHasOptionalDataBeforeCallback($property)
    {
        $this->setExpectedException('BadMethodCallException');
        $has = 'has' . $property;
        $this->response->$has();
    }

    /**
     * Test that any optional parameters are simply passed to the callback stack (when there is at least one), until the
     * value is found (or return the last callback's data).
     *
     * @dataProvider getOptionalProperties
     * @param $property
     */
    public function testOptionalDataProxiesCallback($property)
    {
        $has = 'has' . $property;
        $get = 'get' . $property;

        $callback = $this->getMockBuilder('Nexmo\Network\Number\Callback')
                         ->disableOriginalConstructor()
                         ->setMethods(array('getId', $has, $get))
                         ->getMock();

        //setup so the request will accept the callback
        $callback->expects($this->any())
                 ->method('getId')
                 ->will($this->returnValue($this->data['request_id']));

        $callback->expects($this->atLeastOnce())
                 ->method($has)
                 ->will($this->returnCallback(function(){
                     static $called = false;
                     if(!$called){
                         $called = true;
                         return false;
                     }

                     return true;
                 }));

        $callback->expects($this->atLeastOnce())
                 ->method($get)
                 ->will($this->returnCallback(function(){
                     static $called = false;
                     if(!$called){
                         $called = true;
                         return null;
                     }

                     return 'data';
                 }));

        $response = new Response($this->data, array($callback, $callback));

        $this->assertTrue($response->$has());
        $this->assertEquals('data', $response->$get());
    }

    public function getOptionalProperties()
    {
        return array(
            array('Type'),
            array('Network'),
            array('NetworkName'),
            array('Valid'),
            array('Ported'),
            array('Reachable'),
            array('Roaming'),
            array('RoamingCountry'),
            array('RoamingNetwork'),
        );
    }

}
 