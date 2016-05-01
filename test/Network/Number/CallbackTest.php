<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Network\Number;


class CallbackTest extends \PHPUnit_Framework_TestCase
{
    protected $data = array(
        'request_id' => '12345',
        'callback_total_parts' => 2,
        'callback_part' => 2,
        'number' => '14443332121',
        'status' => 0
    );

    /**
     * @var Callback
     */
    protected $callback;

    public function setup()
    {
        $this->callback = new Callback($this->data);
    }

    public function testMethodsMatchData()
    {
        $this->assertEquals($this->data['request_id'], $this->callback->getId());
        $this->assertEquals($this->data['callback_total_parts'], $this->callback->getCallbackTotal());
        $this->assertEquals($this->data['callback_part'], $this->callback->getCallbackIndex());
        $this->assertEquals($this->data['number'], $this->callback->getNumber());
    }

    /**
     * @dataProvider optionalData
     * @param $key
     * @param $value
     * @param $method
     */
    public function testOptionalData($key, $value, $method, $expected)
    {
        $has = 'has' . $method;
        $get = 'get' . $method;
        $this->assertFalse($this->callback->$has());
        $this->assertNull($this->callback->$get());

        $callback = new Callback(array_merge($this->data, array($key => $value)));

        $this->assertTrue($callback->$has());
        $this->assertEquals($expected, $callback->$get());
    }

    public function optionalData()
    {
        return array(
            array('number_type', 'unknown', 'Type', 'unknown'),
            array('carrier_network_code', 'CODE', 'Network', 'CODE'),
            array('carrier_network_name', 'NAME', 'NetworkName', 'NAME'),
            array('valid', 'unknown', 'Valid', 'unknown'),
            array('ported', 'unknown', 'Ported', 'unknown'),
            array('reachable', 'unknown', 'Reachable', 'unknown'),
            array('roaming', 'unknown', 'Roaming', 'unknown'),
            array('roaming_country_code', 'CODE', 'RoamingCountry', 'CODE'),
            array('roaming_network_code', 'CODE', 'RoamingNetwork', 'CODE'),
        );
    }
}
 