<?php

namespace Vonage\Verify2\Request;

use Vonage\Verify2\VerifyObjects\VerificationLocale;
use Vonage\Verify2\VerifyObjects\VerificationWorkflow;

interface RequestInterface
{
    public function setLocale(VerificationLocale $verificationLocale): static;
    public function setTimeout(int $timeout): static;
    public function setClientRef(string $clientRef): static;
    public function setLength(int $length): static;
    public function setBrand(string $brand): static;
    public function addWorkflow(VerificationWorkflow $verificationWorkflow): static;
    public function getLocale(): ?VerificationLocale;
    public function getTimeout(): int;
    public function getClientRef(): ?string;
    public function getLength(): int;
    public function getBrand(): string;
    public function getWorkflows(): array;
    public function getBaseVerifyUniversalOutputArray(): array;
    public function setCode(string $code): static;
    public function getCode(): ?string;
}