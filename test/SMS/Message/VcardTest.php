<?php
declare(strict_types=1);

namespace VonageTest\SMS\Message;

use Vonage\SMS\Message\Vcard;
use PHPUnit\Framework\TestCase;

class VcardTest extends TestCase
{
    public function testCanCreateVcardMessage()
    {
        $card = 'BEGIN%3aVCARD%0d%0aVERSION%3a2.1%0d%0aFN%3aFull+Name%0d%0aTEL%3a%2b12345678%0d%0aEMAIL%3ainfo%40acme.com%0d%0aURL%3awww.acme.com%0d%0aEND%3aVCARD';
        $message = new Vcard(
            '447700900000',
            '16105551212',
            $card
        );

        $data = $message->toArray();

        $this->assertSame('447700900000', $data['to']);
        $this->assertSame('16105551212', $data['from']);
        $this->assertSame($card, $data['vcard']);
    }
}
