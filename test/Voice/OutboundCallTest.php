<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice;

use InvalidArgumentException;
use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\Phone;
use Vonage\Voice\OutboundCall;

class OutboundCallTest extends VonageTestCase
{
    public function testMachineDetectionThrowsExceptionOnBadValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown machine detection action');

        (new OutboundCall(new Phone('15555555555'), new Phone('16666666666')))
            ->setMachineDetection('bob');
    }
}
