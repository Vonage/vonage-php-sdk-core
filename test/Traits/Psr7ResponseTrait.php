<?php

declare(strict_types=1);

namespace VonageTest\Traits;

use Laminas\Diactoros\Response;

use function fopen;

trait Psr7ResponseTrait
{
    /**
     * Get the API response we'd expect for a call to the API. Message API currently returns 200 all the time, so only
     * change between success / fail is body of the message.
     */
    protected function getResponse(string $type = 'success'): Response
    {
        return new Response(fopen(__DIR__ . '/responses/' . $type . '.json', 'rb'));
    }
}
