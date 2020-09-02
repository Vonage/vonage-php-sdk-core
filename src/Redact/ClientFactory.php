<?php
declare(strict_types=1);

namespace Vonage\Redact;

use Vonage\Client\APIResource;
use Vonage\Client\APIExceptionHandler;
use Psr\Container\ContainerInterface;

/**
 * @todo Finish this Namespace
 */
class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setBaseUri('/v1/redact/transaction')
            ->setCollectionName('')
        ;

        // This API has a slightly different format for the error message, so override
        $exceptionHandler = $api->getExceptionErrorHandler();
        if ($exceptionHandler instanceof APIExceptionHandler) {
            $exceptionHandler->setRfc7807Format("%s - %s. See %s for more information");
        }
        $api->setExceptionErrorHandler($exceptionHandler);

        return new Client($api);
    }
}
