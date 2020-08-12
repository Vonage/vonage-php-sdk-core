<?php
declare(strict_types=1);

namespace VonageTest\Voice\NCCO;

use Vonage\Voice\NCCO\NCCOFactory;
use PHPUnit\Framework\TestCase;

class NCCOFactoryTest extends TestCase
{
    public function testThrowsExceptionWithBadAction()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown NCCO Action foo");

        $factory = new NCCOFactory();
        $factory->build(['action' => 'foo']);
    }
}
