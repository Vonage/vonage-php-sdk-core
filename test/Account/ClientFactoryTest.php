<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Test\Account;

use PHPUnit\Framework\TestCase;
use Vonage\Account\ClientFactory;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Factory\MapFactory;

class ClientFactoryTest extends TestCase
{
    /**
     * @var MapFactory
     */
    protected $mapFactory;

    /**
     * @var mixed
     */
    protected $vonageClient;

    public function setUp(): void
    {
        $this->vonageClient = $this->prophesize(Client::class);
        $this->vonageClient->getRestUrl()->willReturn('https://rest.nexmo.com');
        $this->vonageClient->getApiUrl()->willReturn('https://api.nexmo.com');

        /** @noinspection PhpParamsInspection */
        $this->mapFactory = new MapFactory([APIResource::class => APIResource::class], $this->vonageClient->reveal());
    }

    public function testURIsAreCorrect(): void
    {
        $factory = new ClientFactory();
        $client = $factory($this->mapFactory);

        self::assertSame('/accounts', $client->getSecretsAPI()->getBaseUri());
        self::assertSame('https://api.nexmo.com', $client->getSecretsAPI()->getBaseUrl());
        self::assertSame('/account', $client->getAccountAPI()->getBaseUri());
        self::assertSame('https://rest.nexmo.com', $client->getAccountAPI()->getBaseUrl());
    }
}
