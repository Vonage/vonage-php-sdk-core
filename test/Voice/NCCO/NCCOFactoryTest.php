<?php

declare(strict_types=1);

namespace VonageTest\Voice\NCCO;

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\NCCOFactory;
use Vonage\Voice\NCCO\Action\Wait;

class NCCOFactoryTest extends VonageTestCase
{
    public function testThrowsExceptionWithBadAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown NCCO Action foo");

        $factory = new NCCOFactory();
        $factory->build(['action' => 'foo']);
    }

    public function testBuildsWaitAction(): void
    {
        $factory = new NCCOFactory();
        $action = $factory->build(['action' => 'wait', 'timeout' => 5.0]);

        $this->assertInstanceOf(Wait::class, $action);
        $this->assertSame(['action' => 'wait', 'timeout' => 5.0], $action->toNCCOArray());
    }

    public function testBuildsWaitActionWithoutTimeout(): void
    {
        $factory = new NCCOFactory();
        $action = $factory->build(['action' => 'wait']);

        $this->assertInstanceOf(Wait::class, $action);
        $this->assertSame(['action' => 'wait'], $action->toNCCOArray());
    }
}
