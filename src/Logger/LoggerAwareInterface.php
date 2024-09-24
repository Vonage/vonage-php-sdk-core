<?php

namespace Vonage\Logger;

use Psr\Log\LoggerInterface;

interface LoggerAwareInterface
{
    public function getLogger(): ?LoggerInterface;

    /**
     * @param int|string $level Level of message that we are logging
     * @param array<mixed> $context Additional information for context
     */
    public function log(int|string $level, string $message, array $context = []): void;

    public function setLogger(LoggerInterface $logger): void;
}
