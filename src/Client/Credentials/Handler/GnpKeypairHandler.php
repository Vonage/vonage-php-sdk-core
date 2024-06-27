<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\Gnp;
use Vonage\Client\Credentials\Keypair;

class GnpKeypairHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        /** @var Keypair $credentials  */
        $credentials = $this->extract(Gnp::class, $credentials);
        $token = $credentials->generateJwt();

        return $request->withHeader('Authorization', 'Bearer ' . $token->toString());
    }
}