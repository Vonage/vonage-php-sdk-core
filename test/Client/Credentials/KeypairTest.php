<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

namespace VonageTest\Client\Credentials;

use Lcobucci\JWT\Signer\Key\InMemory;
use Vonage\Client\Exception\Validation;
use VonageTest\VonageTestCase;
use Vonage\Client\Credentials\Keypair;

use function base64_decode;
use function explode;
use function file_get_contents;
use function json_decode;

class KeypairTest extends VonageTestCase
{
    protected string $key;
    protected string $application = 'c90ddd99-9a5d-455f-8ade-dde4859e590e';

    public function setUp(): void
    {
        $this->key = file_get_contents(__DIR__ . '/test.key');
    }

    public function time(): int
    {
        return 1697209080;
    }

    public function testAsArray(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        $array = $credentials->asArray();
        $this->assertEquals($this->key, $array['key']);
        $this->assertEquals($this->application, $array['application']);
    }

    public function testGetKey(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        $key = $credentials->getKey();
        $this->assertInstanceOf(InMemory::class, $key);
    }

    public function testProperties(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        $this->assertEquals($this->key, $credentials->__get('key'));
        $this->assertEquals($this->application, $credentials->application);
    }

    public function testDefaultJWT(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        $jwt = (string)$credentials->generateJwt()->toString();

        [$header, $payload] = $this->decodeJWT($jwt);

        $this->assertArrayHasKey('typ', $header);
        $this->assertArrayHasKey('alg', $header);
        $this->assertEquals('JWT', $header['typ']);
        $this->assertEquals('RS256', $header['alg']);
        $this->assertArrayHasKey('application_id', $payload);
        $this->assertArrayHasKey('jti', $payload);
        $this->assertEquals($this->application, $payload['application_id']);
    }

    public function testAdditionalClaims(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        $claims = [
            'arbitrary' => [
                'nested' => [
                    'data' => "something"
                ]
            ]
        ];

        $jwt = $credentials->generateJwt($claims);
        [, $payload] = $this->decodeJWT($jwt->toString());

        $this->assertArrayHasKey('arbitrary', $payload);
        $this->assertEquals($claims['arbitrary'], $payload['arbitrary']);
    }

    public function testJtiClaim(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        $claims = [
            'jti' => '9a1b8ca6-4406-4530-9940-3cde9d41de3f'
        ];

        $jwt = $credentials->generateJwt($claims);
        [, $payload] = $this->decodeJWT($jwt->toString());

        $this->assertArrayHasKey('jti', $payload);
        $this->assertEquals($claims['jti'], $payload['jti']);
    }

    public function testTtlClaim(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        $claims = [
            'ttl' => 900
        ];

        $jwt = $credentials->generateJwt($claims);
        [, $payload] = $this->decodeJWT($jwt->toString());

        $this->assertArrayHasKey('exp', $payload);
        $this->assertEquals(time() + 900, $payload['exp']);
    }

    public function testNbfNotSupported(): void
    {
        set_error_handler(static function (int $errno, string $errstr) {
            throw new \Exception($errstr, $errno);
        }, E_USER_WARNING);

        $this->expectExceptionMessage('NotBefore Claim is not supported in Vonage JWT');

        $credentials = new Keypair($this->key, $this->application);

        $claims = [
            'nbf' => time() + 900
        ];

        $jwt = $credentials->generateJwt($claims);
        [, $payload] = $this->decodeJWT($jwt->toString());

        $this->assertArrayHasKey('arbitrary', $payload);
        $this->assertEquals($claims['arbitrary'], $payload['arbitrary']);

        restore_error_handler();
    }

    public function testExpNotSupported(): void
    {
        set_error_handler(static function (int $errno, string $errstr) {
            throw new \Exception($errstr, $errno);
        }, E_USER_WARNING);

        $this->expectExceptionMessage('Expiry date is automatically generated from now and TTL, so cannot be passed in
            as an argument in claims');

        $credentials = new Keypair($this->key, $this->application);

        $claims = [
            'exp' => time() + 900
        ];

        $jwt = $credentials->generateJwt($claims);
        [, $payload] = $this->decodeJWT($jwt->toString());

        $this->assertArrayHasKey('arbitrary', $payload);
        $this->assertEquals($claims['arbitrary'], $payload['arbitrary']);

        restore_error_handler();
    }

    /**
     * @link https://github.com/Vonage/vonage-php-sdk-core/issues/276
     */
    public function testExampleConversationJWTWorks(): void
    {
        $credentials = new Keypair($this->key, $this->application);
        $claims = [
            'sub' => 'apg-cs',
            'acl' => [
                'paths' => [
                    '/*/users/**' => (object) [],
                    '/*/conversations/**' => (object) [],
                    '/*/sessions/**' => (object) [],
                    '/*/devices/**' => (object) [],
                    '/*/image/**' => (object) [],
                    '/*/media/**' => (object) [],
                    '/*/applications/**' => (object) [],
                    '/*/push/**' => (object) [],
                    '/*/knocking/**' => (object) [],
                    '/*/legs/**' => (object) [],
                ]
            ],
        ];

        $jwt = $credentials->generateJwt($claims);
        [, $payload] = $this->decodeJWT($jwt->toString());

        $this->assertArrayHasKey('exp', $payload);
        $this->assertEquals($claims['sub'], $payload['sub']);
    }

    /**
     * @param $jwt
     */
    protected function decodeJWT($jwt): array
    {
        $parts = explode('.', $jwt);

        $this->assertCount(3, $parts);

        $header = json_decode(base64_decode($parts[0]), true);
        $payload = json_decode(base64_decode($parts[1]), true);
        $sig = $parts[2];

        return [
            $header,
            $payload,
            $sig
        ];
    }
}
