<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Client\Credentials;

use Vonage\Client\Credentials\Keypair;
use PHPUnit\Framework\TestCase;

class KeypairTest extends TestCase
{
    protected $key;
    protected $application = 'c90ddd99-9a5d-455f-8ade-dde4859e590e';

    public function setUp(): void
    {
        $this->key = file_get_contents(__DIR__ . '/test.key');
    }

    public function testAsArray()
    {
        $credentials = new Keypair($this->key, $this->application);

        $array = $credentials->asArray();
        $this->assertEquals($this->key,    $array['key']);
        $this->assertEquals($this->application, $array['application']);
    }

    public function testArrayAccess()
    {
        $credentials = new Keypair($this->key, $this->application);

        $this->assertEquals($this->key,    $credentials['key']);
        $this->assertEquals($this->application, $credentials['application']);
    }

    public function testProperties()
    {
        $credentials = new Keypair($this->key, $this->application);

        $this->assertEquals($this->key,    $credentials->key);
        $this->assertEquals($this->application, $credentials->application);
    }

    public function testDefaultJWT()
    {
        $credentials = new Keypair($this->key, $this->application);

        //could use the JWT object, but hope to remove as a dependency
        $jwt = (string) $credentials->generateJwt();

        list($header, $payload, $sig) = $this->decodeJWT($jwt);

        $this->assertArrayHasKey('typ', $header);
        $this->assertArrayHasKey('alg', $header);

        $this->assertEquals('JWT', $header['typ']);
        $this->assertEquals('RS256', $header['alg']);

        $this->assertArrayHasKey('application_id', $payload);
        $this->assertArrayHasKey('jti', $payload);

        $this->assertEquals($this->application, $payload['application_id']);
    }

    public function testAdditionalClaims()
    {
        $credentials = new Keypair($this->key, $this->application);

        $claims = [
            'arbitrary' => [
                'nested' => [
                    'data' => "something"
                ]
            ],
            'nbf' => 900
        ];

        $jwt = $credentials->generateJwt($claims);

        list($header, $payload, $sig) = $this->decodeJWT($jwt);

        $this->assertArrayHasKey('arbitrary', $payload);
        $this->assertEquals($claims['arbitrary'], $payload['arbitrary']);

        $this->assertArrayHasKey('nbf', $payload);
        $this->assertEquals(900, $payload['nbf']);
    }

    protected function decodeJWT($jwt)
    {
        $parts = explode('.', $jwt);
        $this->assertCount(3, $parts);

        $header  = json_decode(base64_decode($parts[0]), true);
        $payload = json_decode(base64_decode($parts[1]), true);
        $sig     = $parts[2];

        return [
            $header,
            $payload,
            $sig
        ];
    }
}
