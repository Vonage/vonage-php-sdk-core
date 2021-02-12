<?php

namespace Vonage\Logger;

use Psr\Log\LoggerInterface;

interface LoggerAwareInterface
{
    public function getLogger(): ?LoggerInterface;

    /**
     * @param string|int $level Level of message that we are logging
     * @param array<mixed> $context Additional information for context
     */
    public function log($level, string $message, array $context = []): void;

    /**
     * @return self
     */
    public function setLogger(LoggerInterface $logger);
}
