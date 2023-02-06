<?php

declare(strict_types=1);

namespace Vonage\Client\Request;

use Vonage\Client\Response\ResponseInterface;

interface WrapResponseInterface
{
    public function wrapResponse(ResponseInterface $response): ResponseInterface;
}
