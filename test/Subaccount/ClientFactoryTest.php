<?php

declare(strict_types=1);

namespace VonageTest\Subaccount;

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use Prophecy\Prophecy\ObjectProphecy;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use VonageTest\Traits\Psr7AssertionTrait;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;
use Vonage\Subaccount\Client as SubaccountClient;
use Vonage\Subaccount\ClientFactory;
use VonageTest\VonageTestCase;

class ClientFactoryTest extends VonageTestCase
{
    use Psr7AssertionTrait;

    protected APIResource $api;

    protected Client|ObjectProphecy $vonageClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getCredentials()->willReturn(
            new Client\Credentials\Basic('abc', 'def'),
        );
        $this->vonageClient = $this->vonageClient->reveal();
    }

    /**
     * Makes sure that the client factory returns the correct object instance
     *
     * @see https://github.com/Vonage/vonage-php-sdk-core/pull/472
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function testFactoryMakeCorrectClient(): void
    {
        $container = new MapFactory(
            [
                APIResource::class => APIResource::class,
            ],
            $this->vonageClient
        );

        $factory = new ClientFactory();
        $client = $factory($container);
        $this->assertInstanceOf(SubaccountClient::class, $client);
    }
}
