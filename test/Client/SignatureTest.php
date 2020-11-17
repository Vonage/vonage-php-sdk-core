<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Client;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Client\Signature;

class SignatureTest extends TestCase
{
    public function testInvalidSignatureMethod(): void
    {
        $fakeAlgo = 'fake_algo';

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('Unknown signature algorithm: ' . $fakeAlgo . '. ' .
            'Expected: md5hash, md5, sha1, sha256, or sha512');

        new Signature(['foo' => 'bar'], 'sig_secret', $fakeAlgo);
    }

    /**
     * @dataProvider hmacSignatureProvider
     *
     * @param $algorithm
     * @param $expected
     *
     * @throws ClientException
     */
    public function testHmacSignature($algorithm, $expected): void
    {
        $data = [
            'api_key' => 'fake_api_key',
            'to' => '14155550100',
            'from' => 'AcmeInc',
            'text' => 'Test From Nexmo',
            'type' => 'text',
            'timestamp' => '1540924779'
        ];
        $secret = '71efab63122f1d179f51c46bac838fb5';
        $signature = new Signature($data, $secret, $algorithm);

        $this->assertEquals($expected, $signature->getSignature());
    }

    public function hmacSignatureProvider(): array
    {
        $data = [];

        $data['md5'] = ['md5', '51CDAFEBB4BBCE9525B195C1617CB8D2'];
        $data['sha1'] = ['sha1', '0162AEC64BC183B2E1256545951FE5639DC98020'];
        $data['sha256'] = ['sha256', '9FEC5EF6D0F2B3D2BB7558B6E4042569823CAB9EA0DD30503472B7B304601975'];
        $data['sha512'] = [
            'sha512',
            '40BD12B9A4B6000AD1138EEFD24FFE9FBD72AEE13C3FA04B32BB69DBC256AD0A04A463B1A9AF666' .
            '0D10F6E1E769EE14B9CFF6A635502E93AFCD0BFAB29F38F87'
        ];

        return $data;
    }

    /**
     * @dataProvider signatures
     *
     * @param $algo
     * @param $sig
     * @param $params
     * @param $secret
     *
     * @throws ClientException
     */
    public function testSignature($algo, $sig, $params, $secret): void
    {
        //a signature is created from a set of parameters and a secret
        $signature = new Signature($params, $secret, $algo);

        //the parameters should ne be changed
        $this->assertEquals($params, $signature->getParams());

        //the signature should be generated correctly
        $this->assertEquals($sig, $signature->getSignature());

        //the signed params should include a sig and timestamp
        $this->assertArrayHasKey('timestamp', $signature->getSignedParams());
        $this->assertArrayHasKey('sig', $signature->getSignedParams());

        $this->assertSame($sig, (string)$signature);

        //signature can validate a string signature, or a set of params that includes a signature
        $this->assertTrue($signature->check($sig));
        if (isset($params['sig'])) {
            $this->assertTrue($signature->check($params));
        }
    }

    /**
     * @return array[]
     */
    public function signatures(): array
    {
        return [
            //inbound
            [
                'md5hash',
                'd2e7b1dc968737c5998ad624e02f90b7',
                [
                    'message-timestamp' => '2013-11-21 15:27:30',
                    'messageId' => '020000001B0FE827',
                    'msisdn' => '14843472194',
                    'text' => 'Test again',
                    'timestamp' => '1385047698',
                    'to' => '13239877404',
                    'type' => 'text'
                ],
                'my_secret_key_for_testing'
            ],
            [
                'md5',
                'DDEBD46008C2D4E93CCE578A332A52D5',
                [
                    'message-timestamp' => '2013-11-21 15:27:30',
                    'messageId' => '020000001B0FE827',
                    'msisdn' => '14843472194',
                    'text' => 'Test again',
                    'timestamp' => '1385047698',
                    'to' => '13239877404',
                    'type' => 'text'
                ],
                'my_secret_key_for_testing'
            ],
            [
                'sha1',
                '27D0D05C2876C7CB1720DBCDBA4D492E1E55C09A',
                [
                    'message-timestamp' => '2013-11-21 15:27:30',
                    'messageId' => '020000001B0FE827',
                    'msisdn' => '14843472194',
                    'text' => 'Test again',
                    'timestamp' => '1385047698',
                    'to' => '13239877404',
                    'type' => 'text'
                ],
                'my_secret_key_for_testing'
            ],
            [
                'sha256',
                'DDB8397C2B90AAC7F3882D306475C9A5058C92322EEF43C92B298B6E0FC0D330',
                [
                    'message-timestamp' => '2013-11-21 15:27:30',
                    'messageId' => '020000001B0FE827',
                    'msisdn' => '14843472194',
                    'text' => 'Test again',
                    'timestamp' => '1385047698',
                    'to' => '13239877404',
                    'type' => 'text'
                ],
                'my_secret_key_for_testing'
            ],
            [
                'sha512',
                'E0D3C650F8C9D1A5C174D10DDDBFB003E561F59B265616208B0487C5D819481CD3C311D59CF6165ECD1139622D5BA3A256C0' .
                'D763AC4A9AD9144B5A426B94FE82',
                [
                    'message-timestamp' => '2013-11-21 15:27:30',
                    'messageId' => '020000001B0FE827',
                    'msisdn' => '14843472194',
                    'text' => 'Test again',
                    'timestamp' => '1385047698',
                    'to' => '13239877404',
                    'type' => 'text'
                ],
                'my_secret_key_for_testing'
            ],
            //is sig is passed, it should be ignored
            [
                'md5hash',
                'd2e7b1dc968737c5998ad624e02f90b7',
                [
                    'message-timestamp' => '2013-11-21 15:27:30',
                    'messageId' => '020000001B0FE827',
                    'msisdn' => '14843472194',
                    'text' => 'Test again',
                    'timestamp' => '1385047698',
                    'to' => '13239877404',
                    'type' => 'text',
                    'sig' => 'd2e7b1dc968737c5998ad624e02f90b7'
                ],
                'my_secret_key_for_testing'
            ],
            //is sig is passed, it should be ignored
            [
                'md5hash',
                'f0bfad43bd90cf1ea1f1525c18ba4dab',
                [
                    'message-timestamp' => '2013-11-21 17:31:42',
                    'messageId' => '030000002A264B8B',
                    'msisdn' => '14843472194',
                    'text' => 'Message test',
                    'timestamp' => '1385055102',
                    'to' => '14849970568',
                    'type' => 'text',
                    'sig' => 'f0bfad43bd90cf1ea1f1525c18ba4dab'
                ],
                ''
            ],
            [
                'md5hash',
                '83c052a82906ec7c116e16f6d92f7eee',
                [
                    'message-timestamp' => '2013-11-21 17:37:31',
                    'messageId' => '030000002A267DBB',
                    'msisdn' => '14843472194',
                    'text' => 'One more time',
                    'timestamp' => '1385055451',
                    'to' => '14849970568',
                    'type' => 'text',
                    'sig' => '83c052a82906ec7c116e16f6d92f7eee'
                ],
                'my_secret_key_for_testing'
            ],

            [
                'md5hash',
                'ff933bf31c79ab3fc6a38d100191c48f',
                [
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
                ],
                'my_secret_key_for_testing'
            ],

            [
                'md5hash',
                'e06d9763e3fd0b9c31beb5fc2fcb011c',
                [
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
                ],
                'my_secret_key_for_testing'
            ],

            //outbound
            [
                'md5hash',
                '17f5e3b22f778ec73464c01d180e9d0f',
                [
                    'api_key' => 'not_a_key',
                    'from' => '12192259404',
                    'text' => 'Test&test=something',
                    'timestamp' => '1490638615',
                    'to' => '14843472194',
                ],
                'my_secret_key_for_testing'
            ],
        ];
    }
}
