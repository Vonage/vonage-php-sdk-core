<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Redact;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIExceptionHandler;
use Vonage\Client\APIResource;

/**
 * @todo Finish this Namespace
 */
class ClientFactory
{
    /**
     * @param ContainerInterface $container
     * @return Client
     */
    public function __invoke(ContainerInterface $container): Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUri('/v1/redact/transaction')
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
