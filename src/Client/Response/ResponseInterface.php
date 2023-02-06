<?php

declare(strict_types=1);

namespace Vonage\Client\Response;

interface ResponseInterface
{
    public function getData(): array;

    public function isError(): bool;

    public function isSuccess(): bool;
}
