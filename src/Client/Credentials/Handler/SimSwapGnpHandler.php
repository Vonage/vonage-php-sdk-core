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
class SimSwapGnpHandler extends AbstractHandler
{
    use Client\ClientAwareTrait;
    use Client\ScopeAwareTrait;

    protected ?string $baseUrl = null;
    protected ?string $tokenUrl = null;

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(?string $baseUrl): SimSwapGnpHandler
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function getTokenUrl(): ?string
    {
        return $this->tokenUrl;
    }

    public function setTokenUrl(?string $tokenUrl): SimSwapGnpHandler
    {
        $this->tokenUrl = $tokenUrl;
        return $this;
    }

    public string $token;

    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        /** @var Gnp $credentials  */
        $credentials = $this->extract(Gnp::class, $credentials);
        $msisdn = $credentials->getMsisdn();

        $api = new APIResource();
        $api->setAuthHandlers(new GnpKeypairHandler());
        $api->setClient($this->getClient());
        $api->setBaseUrl($this->getBaseUrl());

        $response = $api->submit([
            'login_hint' => $msisdn,
            'scope' => $this->getScope()
        ]);

        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        $authReqId = $decoded['auth_req_id'];

        // CAMARA Access Token
        $api->setBaseUrl($this->getTokenUrl());
        $response = $api->submit([
            'grant_type' => 'urn:openid:params:grant-type:ciba',
            'auth_req_id' => $authReqId
        ]);

        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        $token = $decoded['access_token'];

        // Add CAMARA Access Token to request and return to make API call
        return $request->withHeader('Authorization', 'Bearer ' . $token);
    }
}
