<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Voice\NCCO;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\NCCO\NCCOFactory;

class NCCOFactoryTest extends TestCase
{
    public function testThrowsExceptionWithBadAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown NCCO Action foo");

        $factory = new NCCOFactory();
        $factory->build(['action' => 'foo']);
    }
}
