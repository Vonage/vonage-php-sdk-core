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
class GnpHandler extends AbstractHandler
{
    use Client\ClientAwareTrait;
    use Client\ScopeAwareTrait;

    protected const VONAGE_GNP_AUTH_BACKEND_URL = 'https://api-eu.vonage.com/oauth2/bc-authorize';
    protected const VONAGE_GNP_AUTH_TOKEN_URL = 'https://api-eu.vonage.com/oauth2/token';
    protected const VONAGE_GNP_AUTH_TOKEN_GRANT_TYPE = 'urn:openid:params:grant-type:ciba';

    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        /** @var Gnp $credentials  */
        $credentials = $this->extract(Gnp::class, $credentials);
        $msisdn = $credentials->getMsisdn();

        // Request OIDC, returns Auth Request ID
        // Reconfigure new client for GNP Auth
        $api = new APIResource();
        $api->setAuthHandlers(new KeypairHandler());
        $api->setClient($this->getClient());
        $api->setBaseUrl(self::VONAGE_GNP_AUTH_BACKEND_URL);

        // This handler requires an injected client configured with a Gnp credentials object and a configured scope
        $response = $api->submit([
            'login_hint' => $msisdn,
            'scope' => $this->getScope()
        ]);

        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        $authReqId = $decoded['auth_req_id'];

        // CAMARA Access Token
        $api->setBaseUrl(self::VONAGE_GNP_AUTH_TOKEN_URL);
        $response = $api->submit([
            'grant_type' => self::VONAGE_GNP_AUTH_TOKEN_GRANT_TYPE,
            'auth_req_id' => $authReqId
        ]);

        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        $token = $decoded['access_token'];

        // Add CAMARA Access Token to request and return to make API call
        return $request->withHeader('Authorization', 'Bearer ' . $token);
    }
}
