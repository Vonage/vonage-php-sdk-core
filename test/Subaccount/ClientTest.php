<?php

declare(strict_types=1);

namespace VonageTest\Subaccount;

use Laminas\Diactoros\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Subaccount\Client as SubaccountClient;
use Vonage\Subaccount\SubaccountObjects\Account;
use VonageTest\Psr7AssertionTrait;
use VonageTest\VonageTestCase;

class ClientTest extends VonageTestCase
{
    use Psr7AssertionTrait;

    protected APIResource $api;

    protected Client|ObjectProphecy $vonageClient;
    protected $subaccountClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Basic('abc', 'def'),
        );

        /** @noinspection PhpParamsInspection */
        $this->api = (new APIResource())
            ->setIsHAL(true)
            ->setErrorsOn200(true)
            ->setClient($this->vonageClient->reveal())
            ->setBaseUrl('https://api.nexmo.com/accounts');

        $this->subaccountClient = new SubaccountClient($this->api);
    }

    public function testClientInitialises(): void
    {
        $this->assertInstanceOf(SubaccountClient::class, $this->subaccountClient);
    }

    public function testCanGetPrimaryAccount(): void
    {
        $apiKey = 'acc6111f';

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/accounts/acc6111f/subaccounts',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('get-success'));

        $response = $this->subaccountClient->getPrimaryAccount($apiKey);
        $this->assertInstanceOf(Account::class, $response);
    }

    public function testCanGetSubaccounts(): void
    {
        $apiKey = 'acc6111f';

        $this->vonageClient->send(Argument::that(function (RequestInterface $request) {
            $uri = $request->getUri();
            $uriString = $uri->__toString();
            $this->assertEquals(
                'https://api.nexmo.com/accounts/acc6111f/subaccounts',
                $uriString
            );

            return true;
        }))->willReturn($this->getResponse('get-success-subaccounts'));

        $response = $this->subaccountClient->getSubaccounts($apiKey);

        foreach ($response as $item) {
            $this->assertInstanceOf(Account::class, $item);
        }
    }

    /**
     * This method gets the fixtures and wraps them in a Response object to mock the API
     */
    protected function getResponse(string $identifier, int $status = 200): Response
    {
        return new Response(fopen(__DIR__ . '/Fixtures/Responses/' . $identifier . '.json', 'rb'), $status);
    }
}
