<?php

declare(strict_types=1);

namespace Vonage\NumberVerification;

use Psr\Http\Client\ClientExceptionInterface;
use Vonage\Client\APIClient;
use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Container;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Gnp;
use Vonage\Client\Exception\Credentials;
use Vonage\Client\Exception\Exception;
use Vonage\Webhook\Factory;

class Client implements APIClient
{
    public function __construct(protected APIResource $api)
    {
    }

    public function getAPIResource(): APIResource
    {
        return $this->api;
    }

    /**
     * You are expected to call this code when you are consuming the webhook that has been
     * received from the frontend call being made
     *
     * @param string $phoneNumber
     * @return bool
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function verifyNumber(string $phoneNumber, string $code, string $state): bool
    {
        /** @var Gnp $credentials */
        $credentials = $this->getAPIResource()->getClient()->getCredentials();

        if ($credentials instanceof Container) {
            $credentials = $credentials->get(Gnp::class);
        }

        $credentials->setCode($code);

        $phoneNumberKey = 'phoneNumber';

        if ($this->isHashedPhoneNumber($phoneNumber)) {
            $phoneNumberKey = 'hashedPhoneNumber';
        }

        // By the time this hits the Number Verification API, the handler will have
        // completed the CAMARA OAuth flow
        $response = $this->getAPIResource()->create(
            [$phoneNumberKey => $phoneNumber],
            'verify'
        );

        return $response['devicePhoneNumberVerified'];
    }

    public function isHashedPhoneNumber(string $phoneNumber): bool
    {
        return (strlen($phoneNumber) >= 15);
    }

    /**
     * This method is the start of the process of Number Verification
     * It builds the correct Front End Auth request for OIDC CAMARA request
     *
     * @param string $phoneNumber
     * @param string $redirectUrl
     * @param string $state
     * @return string
     * @throws Credentials
     */
    public function buildFrontEndUrl(string $phoneNumber, string $redirectUrl, string $state = ''): string
    {
        /** @var Gnp $credentials */
        $credentials = $this->getAPIResource()->getClient()->getCredentials();

        if ($credentials instanceof Container) {
            $credentials = $credentials->get(Gnp::class);
        }

        $this->enforceCredentials($credentials);

        $applicationId = $credentials->getApplication();

        $query = http_build_query([
            'client_id' => $applicationId,
            'redirect_uri' => $redirectUrl,
            'state' => $state,
            'scope' => 'openid dpv:FraudPreventionAndDetection#number-verification-verify-read',
            'response_type' => 'code',
            'login_hint' => $phoneNumber
        ]);

        return 'https://oidc.idp.vonage.com/oauth2/auth' . $query;
    }

    protected function enforceCredentials(CredentialsInterface $credentials): void
    {
        if (!$credentials instanceof Gnp) {
            throw new Credentials('You can only use GNP Credentials with the Number Verification API');
        }
    }
}
