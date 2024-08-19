<?php

declare(strict_types=1);

namespace VonageTest\Voice\NCCO;

use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Record;
use Vonage\Voice\NCCO\NCCO;

use function json_decode;
use function json_encode;

class NCCOTest extends VonageTestCase
{
    public function testCanCreateNCCOFromArray(): void
    {
        $data = [
            [
                'action' => 'talk',
                'bargeIn' => 'false',
                'level' => '0',
                'loop' => '1',
                'text' => 'Thank you for trying Vonage',
                'language' => 'en-US',
                'style' => '0',
                'premium' => 'false'
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

    public function testCanCreateFromValidNCCOArray(): void
    {
        $data = [
            [
                "action" => "talk",
                "text" => "Thank you for trying Vonage",
                "bargeIn" => "false",
                "level" => "0",
                "loop" => "1",
                "language" => "en-US",
                "style" => "0",
                "premium" => "false",
            ],
            [
                "action" => "record",
                "format" => "wav",
                "beepStart" => "true",
                "endOnSilence" => "4",
                "endOnKey" => "#",
                "channels" => "12",
                "split" => "conversation",
                "timeOut" => "7200",
                "eventUrl" => [
                    "http://domain.test/event",
                ],
                "eventMethod" => "POST",
            ],
            [
                "action" => "conversation",
                "name" => "Sample Conversation",
                "startOnEnter" => "true",
                "endOnExit" => "false",
                "record" => "true",
                "canSpeak" => [
                    "49502bca-da71-44bb-b3a6-5077b58c2690",
                ],
                "canHear" => [
                    "798146f0-af79-468a-83a4-b6fcda7cd4e6",
                ],
            ],
            [
                "action" => "connect",
                "endpoint" => [
                    [
                        "type" => "phone",
                        "number" => "447700900001",
                    ],
                ],
            ],
            [
                "action" => "talk",
                "text" => "Thank you for trying Vonage",
                "bargeIn" => "false",
                "level" => "0",
                "loop" => "1",
                "language" => "en-US",
                "style" => "0",
                "premium" => "false",
            ],
            [
                "action" => "record",
                "format" => "wav",
                "beepStart" => "true",
                "endOnSilence" => "4",
                "endOnKey" => "#",
                "channels" => "12",
                "split" => "conversation",
                "timeOut" => "7200",
                "eventUrl" => [
                    "http://domain.test/event",
                ],
                "eventMethod" => "POST",
            ],
            [
                "action" => "conversation",
                "name" => "Sample Conversation",
                "startOnEnter" => "true",
                "endOnExit" => "false",
                "record" => "true",
                "canSpeak" => [
                    "49502bca-da71-44bb-b3a6-5077b58c2690",
                ],
                "canHear" => [
                    "798146f0-af79-468a-83a4-b6fcda7cd4e6",
                ],
            ],
            [
                "action" => "connect",
                "endpoint" => [
                    [
                        "type" => "phone",
                        "number" => "447700900001",
                    ],
                ],
            ],
            [
                "action" => "stream",
                "streamUrl" => [
                    "http://domain.test/music.mp3",
                ],
                "bargeIn" => "true",
                "level" => "0.1",
                "loop" => "0",
            ],
            [
                "action" => "input",
                "dtmf" => [
                    "maxDigits" => 1,
                ],
                "speech" => [
                    "uuid" => [
                        "49502bca-da71-44bb-b3a6-5077b58c2690",
                    ],
                    "maxDuration" => 30,
                ],
            ],
            [
                "action" => "notify",
                "payload" => [
                    "foo" => "bar",
                ],
                "eventUrl" => [
                    "http://domain.test/event",
                ],
                "eventMethod" => "POST",
            ],
        ];
        $ncco = new NCCO();
        $ncco->fromArray($data);
        $this->assertEquals(json_encode($data), json_encode($ncco));
    }

    public function testCanConvertToAndFromArray(): void
    {
        $data = [
            [
                "action" => "talk",
                "text" => "Thank you for trying Vonage",
                "bargeIn" => "false",
                "level" => "0",
                "loop" => "1",
                "language" => "en-US",
                "style" => "0",
                "premium" => "false",
            ],
            [
                "action" => "record",
                "format" => "wav",
                "beepStart" => "true",
                "endOnSilence" => "4",
                "endOnKey" => "#",
                "channels" => "12",
                "split" => "conversation",
                "timeOut" => "7200",
                "eventUrl" => [
                    "http://domain.test/event",
                ],
                "eventMethod" => "POST",
            ],
            [
                "action" => "conversation",
                "name" => "Sample Conversation",
                "startOnEnter" => "true",
                "endOnExit" => "false",
                "record" => "true",
                "canSpeak" => [
                    "49502bca-da71-44bb-b3a6-5077b58c2690",
                ],
                "canHear" => [
                    "798146f0-af79-468a-83a4-b6fcda7cd4e6",
                ],
            ],
            [
                "action" => "connect",
                "endpoint" => [
                    [
                        "type" => "phone",
                        "number" => "447700900001",
                    ],
                ],
            ],
            [
                "action" => "talk",
                "text" => "Thank you for trying Vonage",
                "bargeIn" => "false",
                "level" => "0",
                "loop" => "1",
                "language" => "en-US",
                "style" => "0",
                "premium" => "false",
            ],
            [
                "action" => "record",
                "format" => "wav",
                "beepStart" => "true",
                "endOnSilence" => "4",
                "endOnKey" => "#",
                "channels" => "12",
                "split" => "conversation",
                "timeOut" => "7200",
                "eventUrl" => [
                    "http://domain.test/event",
                ],
                "eventMethod" => "POST",
            ],
            [
                "action" => "conversation",
                "name" => "Sample Conversation",
                "startOnEnter" => "true",
                "endOnExit" => "false",
                "record" => "true",
                "canSpeak" => [
                    "49502bca-da71-44bb-b3a6-5077b58c2690",
                ],
                "canHear" => [
                    "798146f0-af79-468a-83a4-b6fcda7cd4e6",
                ],
            ],
            [
                "action" => "connect",
                "endpoint" => [
                    [
                        "type" => "phone",
                        "number" => "447700900001",
                    ],
                ],
            ],
            [
                "action" => "stream",
                "streamUrl" => [
                    "http://domain.test/music.mp3",
                ],
                "bargeIn" => "true",
                "level" => "0.1",
                "loop" => "0",
            ],
            [
                "action" => "input",
                "dtmf" => [
                    "maxDigits" => 1,
                ],
                "speech" => [
                    "uuid" => [
                        "49502bca-da71-44bb-b3a6-5077b58c2690",
                    ],
                    "maxDuration" => 30,
                ],
            ],
            [
                "action" => "notify",
                "payload" => [
                    "foo" => "bar",
                ],
                "eventUrl" => [
                    "http://domain.test/event",
                ],
                "eventMethod" => "POST",
            ],
        ];
        $ncco1 = new NCCO();
        $ncco2 = new NCCO();
        $ncco1->fromArray($data);
        $ncco2->fromArray($ncco1->toArray());
        $this->assertEquals($ncco1->toArray(), $ncco2->toArray());
        $this->assertEquals(json_encode($data), json_encode($ncco2));
    }
}
