<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Verify;

use Psr\Container\ContainerInterface;
use Vonage\Client\APIResource;

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
            ->setIsHAL(false)
            ->setBaseUri('/verify')
            ->setErrorsOn200(true)
            ->setExceptionErrorHandler(new ExceptionErrorHandler());

        return new Client($api);
    }
}
