<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;

class ClientFactory
{

    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUrl($api->getClient()->getRestUrl())
            ->setCollectionName('messages')
            ->setIsHAL(false)
            ->setErrorsOn200(true)
            ->setExceptionErrorHandler(new ExceptionErrorHandler());

        return new Client($api);
    }
}
