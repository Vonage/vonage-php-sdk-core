<?php

namespace Vonage\Client\Credentials\Handler;

use Vonage\Client\Credentials\Basic;
use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;

class TokenBodyHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        $credentials = $this->extract(Basic::class, $credentials);
        $body = $request->getBody();
        $body->rewind();
        $content = $body->getContents();
        $params = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!$params) {
            $params = [];
        }

        $params = array_merge($params, $credentials->asArray());
        $body->rewind();
        $body->write(json_encode($params, JSON_THROW_ON_ERROR));

        return $request;
    }
}
