<?php

declare(strict_types=1);

namespace Vonage\SimSwap;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\GnpHandler;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }


    public function checkSimSwap(string $number, ?int $maxAge = null)
    {
        /** @var GnpHandler $handler */
        $handler = $this->getAPIResource()->getAuthHandlers()[0];

        if (!$handler instanceof GnpHandler) {
            throw new \RuntimeException('SimSwap Client has been misconfigured. Only a GNP Handler can be used');
        }

        $handler->setScope('dpv:FraudPreventionAndDetection#check-sim-swap');

        $payload = [
            'phoneNumber' => $number
        ];

        if (!is_null($maxAge)) {
            $payload['maxAge'] = $maxAge;
        }

        $response = $this->getAPIResource()->create($payload, 'check');

        return $response['swapped'];
    }

    public function checkSimSwapDate(string $number): string
    {
        /** @var GnpHandler $handler */
        $handler = $this->getAPIResource()->getAuthHandlers()[0];

        if (!$handler instanceof GnpHandler) {
            throw new \RuntimeException('SimSwap Client has been misconfigured. Only a GNP Handler can be used');
        }

        $handler->setScope('dpv:FraudPreventionAndDetection#retrieve-sim-swap');

        $response = $this->getAPIResource()->create(['phoneNumber' => $number], 'retrieve-date');

        return $response['latestSimChange'];
    }
}
