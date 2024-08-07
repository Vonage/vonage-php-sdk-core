<?php

namespace VonageTest\Secrets;

use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Vonage\Client as VonageClient;
use Vonage\Client\APIResource;
use Vonage\Secrets\Client;
use Vonage\Secrets\Secret;
use VonageTest\Traits\HTTPTestTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use HTTPTestTrait;

    /**
     * @var Client
     */
    protected $client;

    protected $vonage;

    public function setUp(): void
    {
        $this->responsesDirectory = __DIR__ . '/responses';

        $this->vonage = $this->prophesize(VonageClient::class);
        $this->vonage->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonage->getApiUrl()->willReturn('https://api.nexmo.com');

        $this->vonage->getCredentials()->willReturn(
            new VonageClient\Credentials\Basic('abc', 'def')
        );

        $api = new APIResource();
        $api->setClient($this->vonage->reveal())
            ->setBaseUri('/accounts')
            ->setAuthHandlers(new VonageClient\Credentials\Handler\BasicHandler())
            ->setCollectionName('secrets');

        $this->client = new Client($api);
    }

    public function testListAllSecrets()
    {
        $this->vonage->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/accounts/abcd123/secrets', 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('list', 200));

        $response = $this->client->list('abcd123');

        $this->assertCount(2, $response);
        foreach ($response as $i => $secret) {
            $this->assertInstanceOf(Secret::class, $secret);
            $this->assertSame($i, $secret->getId());
        }
    }

    public function testGetSecret()
    {
        $this->vonage->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/accounts/abcd123/secrets/105abf14-aa00-45a3-9d27-dd19c5920f2c', 'GET', $request);
            return true;
        }))->willReturn($this->getResponse('single', 200));

        $secret = $this->client->get('abcd123', '105abf14-aa00-45a3-9d27-dd19c5920f2c');

        $this->assertSame('105abf14-aa00-45a3-9d27-dd19c5920f2c', $secret->getId());
        $this->assertSame('2020-09-08T21:54:14Z', $secret->getCreatedAt()->format('Y-m-d\TH:i:s\Z'));
    }

    public function testRevokeSecret()
    {
        $this->vonage->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/accounts/abcd123/secrets/105abf14-aa00-45a3-9d27-dd19c5920f2c', 'DELETE', $request);
            return true;
        }))->willReturn($this->getResponse('empty', 204));

        $secret = $this->client->revoke('abcd123', '105abf14-aa00-45a3-9d27-dd19c5920f2c');
    }

    public function testCreateSecret()
    {
        $this->vonage->send(Argument::that(function (RequestInterface $request) {
            $this->assertRequestUrl('api.nexmo.com', '/accounts/abcd123/secrets', 'POST', $request);
            return true;
        }))->willReturn($this->getResponse('new', 204));

        $secret = $this->client->create('abcd123', '105abf14-aa00-45a3-9d27-dd19c5920f2c');

        $this->assertSame('527ffe03-dfba-46c4-9b40-da5cbefb22c4', $secret->getId());
        $this->assertSame('2020-09-08T21:54:14Z', $secret->getCreatedAt()->format('Y-m-d\TH:i:s\Z'));
    }
}
