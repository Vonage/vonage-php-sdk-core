<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Secrets;

use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Factory\FactoryInterface;

class ClientFactory
{
    public function __invoke(FactoryInterface $container): Client
    {
        $api = $container->make(APIResource::class);
        $api->setBaseUri('/accounts')
            ->setAuthHandler(new BasicHandler())
            ->setCollectionName('secrets');

        return new Client($api);
    }
}
