<?php
declare(strict_types=1);

namespace VonageTest\Voice\NCCO;

use Vonage\Voice\NCCO\Action\Record;
use Vonage\Voice\NCCO\NCCO;
use PHPUnit\Framework\TestCase;

class NCCOTest extends TestCase
{
    public function testCanCreateNCCOFromArray()
    {
        $data = [
            [
                'action' => 'talk',
                'bargeIn' => 'false',
                'level' => 0,
                'loop' => 1,
                'text' => 'Thank you for trying Vonage',
                'voiceName' => 'kimberly'
            ],
            [
                'action' => 'record',
                'format' => Record::FORMAT_OGG,
                'split' => Record::SPLIT,
                'endOnSilence' => '4',
                'endOnKey' => '#',
                'timeOut' => 7200,
                'beepStart' => 'true',
                'channels' => 12,
                'eventUrl' => 'http://domain.test/event',
            ],
            [
                'action' => 'conversation',
                'name' => 'Sample Conversation',
                'musicOnHold' => 'http://domain.test/music.mp3',
                'startOnEnter' => 'true',
                'endOnExit' => 'false',
                'record' => 'true',
                'canSpeak' => ['49502bca-da71-44bb-b3a6-5077b58c2690'],
                'canHear' => ['798146f0-af79-468a-83a4-b6fcda7cd4e6'],
            ],
            [
                'action' => 'connect',
                'endpoint' => [
                    [
                        'type' => 'phone',
                        'number' => '447700900001',
                    ]
                ]
            ],
            [
                'action' => 'stream',
                'streamUrl' => 'http://domain.test/music.mp3',
                'loop' => 0,
                'bargeIn' => true,
                'level' => 0.1
            ],
            [
                'action' => 'input',
                'dtmf' => [
                    'maxDigits' => 1,
                ],
                'speech' => [
                    'uuid' => ['49502bca-da71-44bb-b3a6-5077b58c2690'],
                    'maxDuration' => 30,
                ]
            ],
            [
                'action' => 'notify',
                'payload' => ['foo' => 'bar'],
                'eventUrl' => 'http://domain.test/event',
                'eventMethod' => 'POST',
            ]
        ];

        $ncco = new NCCO();
        $ncco->fromArray($data);

        $json = json_decode(json_encode($ncco), true);

        $this->assertCount(7, $json);
        $this->assertEquals($data[0], $json[0]);
    }
}
