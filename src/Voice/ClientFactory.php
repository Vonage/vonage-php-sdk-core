<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Voice;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\KeypairHandler;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUri('/v1/calls')
            ->setAuthHandler(new KeypairHandler())
            ->setCollectionName('calls');

        return new Client($api);
    }
}
