<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\SMS;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Credentials\Handler\SignatureBodyHandler;

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
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
            ->setAuthHandler([new BasicHandler(), new SignatureBodyHandler()]);

        return new Client($api);
    }
}
