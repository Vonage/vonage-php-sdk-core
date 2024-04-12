<?php

declare(strict_types=1);

namespace Vonage\SMS\Message;

interface Message
{
    public function toArray(): array;
    public function getErrorMessage(): ?string;
    public function getWarningMessage(): ?string;
    public function setWarningMessage(?string $errorMessage): void;
}
