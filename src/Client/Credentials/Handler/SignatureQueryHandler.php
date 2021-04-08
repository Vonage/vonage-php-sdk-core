<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Signature;

class SignatureQueryHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials)
    {
        $credentials = $this->extract(SignatureSecret::class, $credentials);

        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $query['api_key'] = $credentials['api_key'];
        $signature = new Signature($query, $credentials['signature_secret'], $credentials['signature_method']);
        $request = $request->withUri(
            $request->getUri()->withQuery(http_build_query($signature->getSignedParams()))
        );

        return $request;
    }
}