<?php
declare(strict_types=1);

namespace Vonage\Verify;

use Vonage\Client\APIResource;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        /** @var APIResource $api */
        $api = $container->make(APIResource::class);
        $api
            ->setIsHAL(false)
            ->setBaseUri('/verify')
            ->setErrorsOn200(true)
            ->setExceptionErrorHandler(new ExceptionErrorHandler())
        ;

        return new Client($api);
    }
}
