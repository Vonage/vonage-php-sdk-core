<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Keypair;

class KeypairHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials)
    {
        /** @var Keypair $credentials  */
        $credentials = $this->extract(Keypair::class, $credentials);
        $token = $credentials->generateJwt();

        return $request->withHeader('Authorization', 'Bearer ' . $token->toString());
    }
}