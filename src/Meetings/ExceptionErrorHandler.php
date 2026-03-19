<?php

declare(strict_types=1);

namespace Vonage\Meetings;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Vonage\Client\Exception\ConflictException;
use Vonage\Client\Exception\CredentialsException;
use Vonage\Client\Exception\NotFoundException;
use Vonage\Client\Exception\ValidationException;

class ExceptionErrorHandler
{
    public function __invoke(ResponseInterface $response, RequestInterface $request): void
    {
        match ($response->getStatusCode()) {
            400 => throw new ValidationException('The request data was invalid'),
            403 => throw new CredentialsException('You are not authorised to perform this request'),
            404 => throw new NotFoundException('No resource found'),
            409 => throw new ConflictException('Entity conflict')
        };
    }
}
