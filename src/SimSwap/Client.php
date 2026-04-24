<?php

declare(strict_types=1);

namespace Vonage\SimSwap;

use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\SimSwapGnpHandler;

class Client
{
    public function __construct(protected APIResource $api)
    {
    }

    public function checkSimSwap(string $number, ?int $maxAge = null)
    {
        /** @var SimSwapGnpHandler $handler */
        $handler = $this->api->getAuthHandlers()[0];
        $handler->setScope('dpv:FraudPreventionAndDetection#check-sim-swap');

        if (!$handler instanceof SimSwapGnpHandler) {
            throw new \RuntimeException('SimSwap Client has been misconfigured. Only a GNP Handler can be used');
        }

        $payload = [
            'phoneNumber' => $number
        ];

        if (!is_null($maxAge)) {
            $payload['maxAge'] = $maxAge;
        }

        $response = $this->api->create($payload, 'check');

        return $response['swapped'];
    }

    public function checkSimSwapDate(string $number): string
    {
        /** @var SimSwapGnpHandler $handler */
        $handler = $this->api->getAuthHandlers()[0];
        $handler->setScope('dpv:FraudPreventionAndDetection#retrieve-sim-swap-date');

        if (!$handler instanceof SimSwapGnpHandler) {
            throw new \RuntimeException('SimSwap Client has been misconfigured. Only a GNP Handler can be used');
        }

        $response = $this->api->create(['phoneNumber' => $number], 'retrieve-date');

        return $response['latestSimChange'];
    }
}
