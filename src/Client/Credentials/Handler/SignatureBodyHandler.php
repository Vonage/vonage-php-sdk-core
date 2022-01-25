<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Signature;

class SignatureBodyHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        $credentials = $this->extract(SignatureSecret::class, $credentials);
        $credentialsArray = $credentials->asArray();

        $body = $request->getBody();
        $body->rewind();
        $content = $body->getContents();
        $params = json_decode($content, true);
        $params['api_key'] = $credentialsArray['api_key'];

        $signature = new Signature(
            $params,
            $credentialsArray['signature_secret'],
            $credentialsArray['signature_method']
        );

        $body->rewind();
        $body->write(json_encode($signature->getSignedParams()));

        return $request;
    }
}
