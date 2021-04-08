<?php

namespace Vonage\Client\Credentials\Handler;

use Vonage\Client\Credentials\Basic;
use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;

class TokenBodyFormHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials)
    {
        $credentials = $this->extract(Basic::class, $credentials);
        $body = $request->getBody();
        $body->rewind();
        $content = $body->getContents();
        $params = [];
        parse_str($content, $params);
        $params = array_merge($params, $credentials->asArray());
        $body->rewind();
        $body->write(http_build_query($params, '', '&'));

        return $request;
    }
}