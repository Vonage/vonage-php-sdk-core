<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Voice;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\OutboundCall;

class OutboundCallTest extends TestCase
{
    public function testMachineDetectionThrowsExceptionOnBadValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown machine detection action');

        (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setMachineDetection('bob');
    }
}
