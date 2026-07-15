<?php

declare(strict_types=1);

namespace Vonage\SimSwap;

use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Handler\SimSwapGnpHandler;

/**
 * @deprecated The SimSwap API and its SDK support are being removed in the next major version.
 *             Refer to the Vonage Network APIs documentation for the replacement.
 */
class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
        trigger_error(
            'Vonage\\SimSwap\\Client is deprecated and will be removed in the next major version. '
            . 'Refer to the Vonage Network APIs documentation for the replacement.',
            E_USER_DEPRECATED
        );
    }

    /**
     * @deprecated The SimSwap API is being removed in the next major version.
     */
    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    /**
     * @deprecated The SimSwap API is being removed in the next major version.
     */
    public function checkSimSwap(string $number, ?int $maxAge = null)
    {
        trigger_error(
            'Vonage\\SimSwap\\Client is deprecated and will be removed in the next major version. ' .
            'Refer to the Vonage Network APIs for the replacement.',
            E_USER_DEPRECATED
        );
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

    /**
     * @deprecated The SimSwap API is being removed in the next major version.
     */
    public function checkSimSwapDate(string $number): string
    {
        trigger_error(
            'Vonage\\SimSwap\\Client is deprecated and will be removed in the next major version. ' .
            'Refer to the Vonage Network APIs for the replacement.',
            E_USER_DEPRECATED
        );
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
