<?php

namespace Vonage\Logger;

use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param string|int $level Level of message that we are logging
     * @param array<mixed> $context Additional information for context
     */
    public function log($level, string $message, array $context = []): void
    {
        $logger = $this->getLogger();
        if ($logger) {
            $logger->log($level, $message, $context);
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
