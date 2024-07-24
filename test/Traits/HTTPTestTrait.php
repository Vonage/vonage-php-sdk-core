<?php

namespace VonageTest\Traits;

use Laminas\Diactoros\Response;

trait HTTPTestTrait
{
    use Psr7AssertionTrait;

    protected string $responsesDirectory;

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen($this->responsesDirectory . '/' . $type . '.json', 'rb'), $status);
    }
}
