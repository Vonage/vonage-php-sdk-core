<?php

namespace Vonage\Verify2;

use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\BasicHandler;
use Vonage\Client\Credentials\Handler\KeypairHandler;

class ClientFactory
{
    public function __invoke(): Client
    {
        $api = $container->make(APIResource::class);
        $api->setIsHAL(false)
            ->setErrorsOn200(false)
            ->setClient($this->vonageClient->reveal())
            ->setAuthHandler([new BasicHandler(), new KeypairHandler()])
            ->setBaseUrl('https://api.nexmo.com/v2/verify/');

        return new Client($api);
    }
}