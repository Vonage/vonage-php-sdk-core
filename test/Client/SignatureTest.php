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
            //inbound
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
            ), 'my_secret_key_for_testing'),

            array('ff933bf31c79ab3fc6a38d100191c48f', array(
                'keyword' => 'TESTINGS',
                'message-timestamp' => '2017-04-04 23:05:22',
                'messageId' => '0C00000027217D5B',
                'msisdn' => '14843472194',
                'nonce' => 'c4ab6ed2-9bf5-48a0-af91-107e29bdd399',
                'sig' => 'ff933bf31c79ab3fc6a38d100191c48f',
                'text' => 'Testings',
                'timestamp' => '1491347122',
                'to' => '12192259404',
                'type' => 'text',
            ), 'my_secret_key_for_testing'),

            array('e06d9763e3fd0b9c31beb5fc2fcb011c', array(
                'keyword' => 'TEST',
                'message-timestamp' => '2017-04-04 22:57:47',
                'messageId' => '0B00000042AC53BD',
                'msisdn' => '14843472194',
                'nonce' => '929d6744-bd28-42c8-b6cf-31d5b4f43732',
                'sig' => 'e06d9763e3fd0b9c31beb5fc2fcb011c',
                'text' => 'Test with & and =',
                'timestamp' => '1491346667',
                'to' => '12192259404',
                'type' => 'text'
            ), 'my_secret_key_for_testing'),

            //outbound
            array('17f5e3b22f778ec73464c01d180e9d0f', array(
                'api_key' => 'not_a_key',
                'from' => '12192259404',
                'text' => '14843472194',
                'text' => 'Test&test=something',
                'timestamp' => '1490638615',
                'to' => '14843472194',
            ), 'my_secret_key_for_testing'),
        );
    }

}
 