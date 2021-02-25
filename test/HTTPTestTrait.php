<?php

namespace VonageTest;

use Laminas\Diactoros\Response;

trait HTTPTestTrait
{
    use Psr7AssertionTrait;

    /**
     * Get the API response we'd expect for a call to the API.
     */
    protected function getResponse(string $type = 'success', int $status = 200): Response
    {
        return new Response(fopen($this->responsesDir . '/' . $type . '.json', 'rb'), $status);
    }
}
