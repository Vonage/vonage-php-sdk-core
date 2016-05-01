<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Client;
use Nexmo\Client\Signature;

class SignatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider signatures
     * @param $sig
     * @param $params
     * @param $secret
     */
    public function testSignature($sig, $params, $secret)
    {
        //a signature is created from a set of parameters and a secret
        $signature = new Signature($params, $secret);

        //the parameters should ne be changed
        $this->assertEquals($params, $signature->getParams());

        //the signature should be generated correctly
        $this->assertEquals($sig, $signature->getSignature());

        //the signed params should include a sig and timestamp
        $this->assertArrayHasKey('timestamp', $signature->getSignedParams());
        $this->assertArrayHasKey('sig', $signature->getSignedParams());

        $this->assertSame($sig, (string) $signature);

        //signature can validate a string signature, or a set of params that includes a signature
        $this->assertTrue($signature->check($sig));
        if(isset($params['sig'])){
            $this->assertTrue($signature->check($params));
        }
    }

    public function signatures()
    {
        return array(
            //real signature
            array('d2e7b1dc968737c5998ad624e02f90b7', array(
                'message-timestamp' => '2013-11-21 15:27:30',
                'messageId' => '020000001B0FE827',
                'msisdn' => '14843472194',
                'text' => 'Test again',
                'timestamp' => '1385047698',
                'to' => '13239877404',
                'type' => 'text'
            ),'my_secret_key_for_testing'),
            //is sig is passed, it should be ignored
            array('d2e7b1dc968737c5998ad624e02f90b7', array(
                'message-timestamp' => '2013-11-21 15:27:30',
                'messageId' => '020000001B0FE827',
                'msisdn' => '14843472194',
                'text' => 'Test again',
                'timestamp' => '1385047698',
                'to' => '13239877404',
                'type' => 'text',
                'sig' => 'd2e7b1dc968737c5998ad624e02f90b7'
            ), 'my_secret_key_for_testing'),
            //is sig is passed, it should be ignored
            array('f0bfad43bd90cf1ea1f1525c18ba4dab', array(
                'message-timestamp' => '2013-11-21 17:31:42',
                'messageId' => '030000002A264B8B',
                'msisdn' => '14843472194',
                'text' => 'Message test',
                'timestamp' => '1385055102',
                'to' => '14849970568',
                'type' => 'text',
                'sig' => 'f0bfad43bd90cf1ea1f1525c18ba4dab'
            ), ''),
            array('83c052a82906ec7c116e16f6d92f7eee', array(
                'message-timestamp' => '2013-11-21 17:37:31',
                'messageId' => '030000002A267DBB',
                'msisdn' => '14843472194',
                'text' => 'One more time',
                'timestamp' => '1385055451',
                'to' => '14849970568',
                'type' => 'text',
                'sig' => '83c052a82906ec7c116e16f6d92f7eee'
            ), 'my_secret_key_for_testing')
        );
    }

}
 