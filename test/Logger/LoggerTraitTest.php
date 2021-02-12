<?php

namespace VonageTest\Logger;

use Psr\Log\LoggerInterface;
use Vonage\Logger\LoggerTrait;
use PHPUnit\Framework\TestCase;

class LoggerTraitTest extends TestCase
{
    public function testCanSetAndGetLogger()
    {
        /** @var LoggerTrait $trait */
        $trait = $this->getMockForTrait(LoggerTrait::class);
        $logger = $this->prophesize(LoggerInterface::class)->reveal();
        $trait->setLogger($logger);

        $this->assertSame($logger, $trait->getLogger());
    }

    public function testNoLoggerReturnsNull()
    {
        /** @var LoggerTrait $trait */
        $trait = $this->getMockForTrait(LoggerTrait::class);

        $this->assertNull($trait->getLogger());
    }

    public function testCanLogMessageWithLogger()
    {
        /** @var LoggerTrait $trait */
        $trait = $this->getMockForTrait(LoggerTrait::class);
        $logger = $this->prophesize(LoggerInterface::class)->reveal();
        $trait->setLogger($logger);

        $this->assertNull($trait->log('debug', 'This is a message'));
    }

    public function testLoggingAcceptsMessageWithLogger()
    {
        /** @var LoggerTrait $trait */
        $trait = $this->getMockForTrait(LoggerTrait::class);

        $this->assertNull($trait->log('debug', 'This is a message'));
    }
}