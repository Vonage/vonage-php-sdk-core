<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice;

use Exception;
use PHPUnit\Framework\TestCase;
use Vonage\Voice\Call;

class CallTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testConvertsToArrayProperly(): void
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/call.json'), true);
        $call = new Call($data);
        $callData = $call->toArray();

        self::assertEquals($data['uuid'], $callData['uuid']);
        self::assertEquals($data['status'], $callData['status']);
        self::assertEquals($data['direction'], $callData['direction']);
        self::assertEquals($data['rate'], $callData['rate']);
        self::assertEquals($data['price'], $callData['price']);
        self::assertEquals($data['duration'], $callData['duration']);
        self::assertEquals($data['start_time'], $callData['start_time']);
        self::assertEquals($data['end_time'], $callData['end_time']);
        self::assertEquals($data['network'], $callData['network']);
        self::assertEquals($data['to'][0]['type'], $callData['to'][0]['type']);
        self::assertEquals($data['to'][0]['number'], $callData['to'][0]['number']);
        self::assertEquals($data['from'][0]['type'], $callData['from'][0]['type']);
        self::assertEquals($data['from'][0]['number'], $callData['from'][0]['number']);
    }
}
