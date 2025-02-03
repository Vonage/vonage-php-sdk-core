<?php

declare(strict_types=1);

namespace VonageTest;

use Lcobucci\JWT\Token\Plain;
use Psr\Http\Client\ClientInterface;
use RuntimeException;
use Vonage\Client\Credentials\CredentialsInterface;
use VonageTest\VonageTestCase;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;

class ClientTest extends VonageTestCase
{
    /**
     * Make sure that when calling the video module it errors if the class isn't found
     */
    public function testCallingVideoWithoutPackageGeneratesRuntimeError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please install @vonage/video to use the Video API');

        $client = new Client(new Basic('abcd', '1234'));
        $video = $client->video();
    }

    public function testConstructorWithValidClient()
    {
        $credentials = $this->createMock(Basic::class);
        $httpClient = $this->createMock(ClientInterface::class);
        $options = ['debug' => true];

        $client = new Client($credentials, $options, $httpClient);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertTrue($client->getDebug());
    }

    public function testConstructorWithoutHttpClientUsesDefault()
    {
        $credentials = $this->createMock(Basic::class);
        $options = ['debug' => true];

        $client = new Client($credentials, $options);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(ClientInterface::class, $client->getHttpClient());
    }

    public function testConstructorThrowsExceptionOnInvalidCredentials()
    {
        $invalidCredentials = $this->createMock(CredentialsInterface::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unknown credentials type');

        new Client($invalidCredentials);
    }

    public function testConstructorWithCustomOptions()
    {
        $credentials = $this->createMock(Basic::class);
        $options = [
            'base_rest_url' => 'https://example-rest.com',
            'base_api_url' => 'https://example-api.com',
            'debug' => true
        ];

        $client = new Client($credentials, $options);

        $this->assertEquals('https://example-rest.com', $client->getRestUrl());
        $this->assertEquals('https://example-api.com', $client->getApiUrl());
        $this->assertTrue($client->getDebug());
    }

    public function testConstructorHandlesDeprecationsOption()
    {
        $credentials = $this->createMock(Basic::class);
        $options = ['show_deprecations' => true];

        $client = new Client($credentials, $options);

        // No specific assertion for error handler setup, but ensuring no exceptions occurred.
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testConstructorHandlesVideoClientFactory()
    {
        $credentials = $this->createMock(Basic::class);

        if (class_exists('Vonage\Video\ClientFactory')) {
            $this->markTestSkipped('Vonage Video ClientFactory class is available.');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Please install @vonage/video to use the Video API');

        $client = new Client($credentials);
        $client->video();
    }

    public function testWillGenerateJwt()
    {
        $keyPath = __DIR__ . '/Client/Credentials/test.key';
        $keyContents = file_get_contents($keyPath);
        $credentials = new Client\Credentials\Keypair($keyContents, 'abc123');
        $client = new Client($credentials);
        $jwt = $client->generateJwt();

        $this->assertInstanceOf(Plain::class, $jwt);
    }
}
