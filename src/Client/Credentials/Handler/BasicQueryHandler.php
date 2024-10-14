<?php

namespace Vonage\Client\Credentials\Handler;

use Psr\Http\Message\RequestInterface;
use Vonage\Client\Credentials\Basic;
use Vonage\Client\Credentials\CredentialsInterface;

class BasicQueryHandler extends AbstractHandler
{
    public function __invoke(RequestInterface $request, CredentialsInterface $credentials): RequestInterface
    {
        $credentials = $this->extract(Basic::class, $credentials);
        parse_str($request->getUri()->getQuery(), $query);
        $query = array_merge($query, $credentials->asArray());

        return $request->withUri($request->getUri()->withQuery(http_build_query($query)));
    }
}
