<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Signature;

class SignatureBodyHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials)
    {
        $credentials = $this->extract(SignatureSecret::class, $credentials);

        $body = $request->getBody();
        $body->rewind();
        $content = $body->getContents();
        $params = json_decode($content, true);
        $params['api_key'] = $credentials['api_key'];
        $signature = new Signature($params, $credentials['signature_secret'], $credentials['signature_method']);
        $body->rewind();
        $body->write(json_encode($signature->getSignedParams()));

        return $request;
    }
}