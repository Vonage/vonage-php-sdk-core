<?php

namespace Vonage\Client\Credentials\Handler;

use Vonage\Client\Credentials\Basic;
use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\CredentialsInterface;

class TokenQueryHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        $credentials = $this->extract(Basic::class, $credentials);
        $query = [];
        parse_str($request->getUri()->getQuery(), $query);
        $query = array_merge($query, $credentials->asArray());

        $request = $request->withUri($request->getUri()->withQuery(http_build_query($query)));

        return $request;
    }
}