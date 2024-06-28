<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Gnp;

/**
 * This handler is for Vonage GNP APIs that require the CAMARA standard OAuth Flow
 */
class NumberVerificationGnpHandler extends SimSwapGnpHandler
{
    use Client\ClientAwareTrait;
    use Client\ScopeAwareTrait;

    protected ?string $baseUrl = null;
    protected ?string $tokenUrl = null;

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(?string $baseUrl): NumberVerificationGnpHandler
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function getTokenUrl(): ?string
    {
        return $this->tokenUrl;
    }

    public function setTokenUrl(?string $tokenUrl): NumberVerificationGnpHandler
    {
        $this->tokenUrl = $tokenUrl;
        return $this;
    }

    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        /** @var Gnp $credentials  */
        $credentials = $this->extract(Gnp::class, $credentials);

        // submit the code to CAMARA endpoint
        $api = new APIResource();
        $api->setAuthHandlers(new GnpKeypairHandler());
        $api->setClient($this->getClient());
        $api->setBaseUrl('https://api-eu.vonage.com/oauth2/token');

        $tokenResponse = $api->submit([
            'grant_type' => 'authorization_code',
            'code' => $credentials->getCode(),
            'redirect_uri' => $credentials->getRedirectUri()
        ]);

        $payload = json_decode($tokenResponse, true);

        // Add CAMARA Access Token to request and return to make API call
        return $request->withHeader('Authorization', 'Bearer ' . $payload['access_token']);
    }
}
