<?php

declare(strict_types=1);

namespace VonageTest\Voice\NCCO;

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\NCCOFactory;

class NCCOFactoryTest extends VonageTestCase
{
    public function testThrowsExceptionWithBadAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown NCCO Action foo");

        $factory = new NCCOFactory();
        $factory->build(['action' => 'foo']);
    }
}
