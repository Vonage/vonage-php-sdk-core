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
     * @param int|string $level Level of message that we are logging
     * @param array<mixed> $context Additional information for context
     */
    public function log(int|string $level, string $message, array $context = []): void
    {
        $logger = $this->getLogger();
        if ($logger) {
            $logger->log($level, $message, $context);
        }
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
