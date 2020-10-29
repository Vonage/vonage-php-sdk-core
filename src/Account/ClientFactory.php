<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Account;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;

class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $accountApi */
        $accountApi = $container->make(APIResource::class);
        $accountApi
            ->setBaseUrl($accountApi->getClient()->getRestUrl())
            ->setIsHAL(false)
            ->setBaseUri('/account')
        ;

        $secretsApi = $container->make(APIResource::class);
        $secretsApi->setBaseUri('/accounts');

        return new Client($accountApi, $secretsApi);
    }
}
