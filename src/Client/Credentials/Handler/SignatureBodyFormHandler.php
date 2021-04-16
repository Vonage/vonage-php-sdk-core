<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Signature;

class SignatureBodyFormHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials)
    {
        $credentials = $this->extract(SignatureSecret::class, $credentials);

        $body = $request->getBody();
        $body->rewind();
        $content = $body->getContents();
        $params = [];
        parse_str($content, $params);
        $params['api_key'] = $credentials['api_key'];
        $signature = new Signature($params, $credentials['signature_secret'], $credentials['signature_method']);
        $params = $signature->getSignedParams();
        $body->rewind();
        $body->write(http_build_query($params, '', '&'));

        return $request;
    }
}