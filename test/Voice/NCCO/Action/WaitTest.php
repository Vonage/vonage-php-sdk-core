<?php

declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Wait;

class WaitTest extends VonageTestCase
{
    public function testDefaultSetupOmitsTimeout(): void
    {
        $this->assertSame(
            ['action' => 'wait'],
            (new Wait())->toNCCOArray()
        );
    }

    public function testTimeoutIsIncludedWhenSet(): void
    {
        $this->assertSame(
            ['action' => 'wait', 'timeout' => 0.5],
            (new Wait())->setTimeout(0.5)->toNCCOArray()
        );
    }

    public function testJsonSerializeMatchesToNCCOArray(): void
    {
        $wait = (new Wait())->setTimeout(5.0);

        $this->assertSame($wait->toNCCOArray(), $wait->jsonSerialize());
    }

    public function testFactoryWithoutTimeout(): void
    {
        $wait = Wait::factory([]);

        $this->assertSame(['action' => 'wait'], $wait->toNCCOArray());
        $this->assertNull($wait->getTimeout());
    }

    public function testFactoryWithTimeout(): void
    {
        $wait = Wait::factory(['timeout' => 10.0]);

        $this->assertSame(['action' => 'wait', 'timeout' => 10.0], $wait->toNCCOArray());
        $this->assertSame(10.0, $wait->getTimeout());
    }

    public function testFactoryWithFloatTimeout(): void
    {
        $wait = Wait::factory(['timeout' => 0.1]);

        $this->assertSame(0.1, $wait->getTimeout());
    }
}
