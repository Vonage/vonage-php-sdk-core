<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Redact;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIExceptionHandler;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;

/**
 * @todo Finish this Namespace
 */
class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUri('/v1/redact/transaction')
            ->setAuthHandler(new BasicHandler())
            ->setCollectionName('');

        // This API has a slightly different format for the error message, so override
        $exceptionHandler = $api->getExceptionErrorHandler();

        if ($exceptionHandler instanceof APIExceptionHandler) {
            $exceptionHandler->setRfc7807Format("%s - %s. See %s for more information");
        }

        $api->setExceptionErrorHandler($exceptionHandler);

        return new Client($api);
    }
}
