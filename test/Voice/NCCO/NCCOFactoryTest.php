<?php
declare(strict_types=1);

namespace NexmoTest\Voice\NCCO;

use Nexmo\Voice\NCCO\NCCOFactory;
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
