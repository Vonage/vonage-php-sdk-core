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

        // We have to do some clunky body pointer rewinding here
        $existingBody = $request->getBody();
        $existingBody->rewind();
        $existingBodyContent = $existingBody->getContents();
        $existingBody->rewind();
        $existingBodyArray = json_decode($existingBodyContent, true);

        // The request body will now be the existing body plus the basic creds
        $mergedBodyArray = array_merge($existingBodyArray, $credentials->asArray());

        return $request->withBody(\GuzzleHttp\Psr7\Utils::streamFor(json_encode($mergedBodyArray)));
    }
}
