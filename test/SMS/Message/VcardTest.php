<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\SMS\Message;

use PHPUnit\Framework\TestCase;
use Vonage\SMS\Message\Vcard;

class VcardTest extends TestCase
{
    public function testCanCreateVcardMessage(): void
    {
        $card = 'BEGIN%3aVCARD%0d%0aVERSION%3a2.1%0d%0aFN%3aFull+Name%0d%0aTEL%3a%2b12345678%0d%0aEMAIL%3ainfo%40acm ' .
            'e.com%0d%0aURL%3awww.acme.com%0d%0aEND%3aVCARD';

        $data = (new Vcard(
            '447700900000',
            '16105551212',
            $card
        ))->toArray();

        self::assertSame('447700900000', $data['to']);
        self::assertSame('16105551212', $data['from']);
        self::assertSame($card, $data['vcard']);
    }
}
