<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Signature;

class SignatureQueryHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        $credentials = $this->extract(SignatureSecret::class, $credentials);
        $credentialsArray = $credentials->asArray();

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $query['api_key'] = $credentialsArray['api_key'];

        $signature = new Signature(
            $query,
            $credentialsArray['signature_secret'],
            $credentialsArray['signature_method']
        );

        return $request->withUri(
            $request->getUri()->withQuery(http_build_query($signature->getSignedParams()))
        );
    }
}
