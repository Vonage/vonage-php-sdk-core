<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */

namespace Vonage\Test\Client\Credentials;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Credentials\Keypair;

class KeypairTest extends TestCase
{
    protected $key;
    protected $application = 'c90ddd99-9a5d-455f-8ade-dde4859e590e';

    public function setUp(): void
    {
        $this->key = file_get_contents(__DIR__ . '/test.key');
    }

    public function testAsArray(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        $array = $credentials->asArray();
        self::assertEquals($this->key, $array['key']);
        self::assertEquals($this->application, $array['application']);
    }

    public function testArrayAccess(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        self::assertEquals($this->key, $credentials['key']);
        self::assertEquals($this->application, $credentials['application']);
    }

    public function testProperties(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        self::assertEquals($this->key, $credentials->__get('key'));
        self::assertEquals($this->application, $credentials->application);
    }

    public function testDefaultJWT(): void
    {
        $credentials = new Keypair($this->key, $this->application);

        //could use the JWT object, but hope to remove as a dependency
        $jwt = (string)$credentials->generateJwt();

        [$header, $payload] = $this->decodeJWT($jwt);

        self::assertArrayHasKey('typ', $header);
        self::assertArrayHasKey('alg', $header);
        self::assertEquals('JWT', $header['typ']);
        self::assertEquals('RS256', $header['alg']);
        self::assertArrayHasKey('application_id', $payload);
        self::assertArrayHasKey('jti', $payload);
        self::assertEquals($this->application, $payload['application_id']);
    }

    public function testAdditionalClaims(): void
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
        [, $payload] = $this->decodeJWT($jwt);

        self::assertArrayHasKey('arbitrary', $payload);
        self::assertEquals($claims['arbitrary'], $payload['arbitrary']);
        self::assertArrayHasKey('nbf', $payload);
        self::assertEquals(900, $payload['nbf']);
    }

    /**
     * @param $jwt
     * @return array
     */
    protected function decodeJWT($jwt): array
    {
        $parts = explode('.', $jwt);

        self::assertCount(3, $parts);

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
