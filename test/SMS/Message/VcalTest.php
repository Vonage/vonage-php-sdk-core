<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\SMS\Message;

use PHPUnit\Framework\TestCase;
use Vonage\SMS\Message\Vcal;

class VcalTest extends TestCase
{
    public function testCanCreateVcalMessage(): void
    {
        $event = 'BEGIN%3AVCALENDAR%0AVERSION%3A2.0%0APRODID%3A-%2F%2Fhacksw%2Fhandcal%2F%2FNONSGML%20v1.0%2F%2FEN%0A' .
            'BEGIN%3AVEVENT%0AUID%3Auid1%40example.com%0ADTSTAMP%3A19970714T170000Z%0AORGANIZER%3BCN%3DJohn%20Doe%3AM' .
            'AILTO%3Ajohn.doe%40example.com%0ADTSTART%3A19970714T170000Z%0ADTEND%3A19970715T035959Z%0ASUMMARY%3ABasti' .
            'lle%20Day%20Party%0AEND%3AVEVENT%0AEND%3AVCALENDAR';

        $data = (new Vcal(
            '447700900000',
            '16105551212',
            $event
        ))->toArray();

        $this->assertSame('447700900000', $data['to']);
        $this->assertSame('16105551212', $data['from']);
        $this->assertSame($event, $data['vcal']);
    }
}
