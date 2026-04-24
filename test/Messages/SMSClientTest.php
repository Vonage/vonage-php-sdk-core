<?php

namespace VonageTest\Messages;

use Laminas\Diactoros\Request;
use Prophecy\Argument;
use Vonage\Messages\Channel\SMS\SMSText;

class SMSClientTest extends MessagesClientTest
{
    public function testCanSendSMS(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines'
        ];

        $message = new SMSText($payload['to'], $payload['from'], $payload['text']);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertEquals(
                'Bearer ',
                mb_substr($request->getHeaders()['Authorization'][0], 0, 7)
            );
            $this->assertRequestJsonBodyContains('to', $payload['to'], $request);
            $this->assertRequestJsonBodyContains('from', $payload['from'], $request);
            $this->assertRequestJsonBodyContains('text', $payload['text'], $request);
            $this->assertRequestJsonBodyContains('channel', 'sms', $request);
            $this->assertRequestJsonBodyContains('message_type', 'text', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendSMSWithOptionalFields(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'encoding_type' => 'auto',
            'content_id' => '1107457532145798767',
            'entity_id' => '1101456324675322134',
            'webhook_url' => 'https://example.com/status',
            'webhook_version' => 'v1',
            'ttl' => 300
        ];

        $message = new SMSText($payload['to'], $payload['from'], $payload['text']);
        $message->setEncodingType($payload['encoding_type']);
        $message->setTtl($payload['ttl']);
        $message->setContentId($payload['content_id']);
        $message->setEntityId($payload['entity_id']);
        $message->setWebhookUrl($payload['webhook_url']);
        $message->setWebhookVersion($payload['webhook_version']);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('ttl', $payload['ttl'], $request);
            $this->assertRequestJsonBodyContains('webhook_url', $payload['webhook_url'], $request);
            $this->assertRequestJsonBodyContains('webhook_version', $payload['webhook_version'], $request);
            $smsObject = [
                'encoding_type' => $payload['encoding_type'],
                'content_id' => $payload['content_id'],
                'entity_id' => $payload['entity_id']
            ];

            $this->assertRequestJsonBodyContains('sms', $smsObject, $request);

            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testCanSendSMSWtihFailover(): void
    {
        $payload = [
            'to' => '447700900000',
            'from' => '16105551212',
            'text' => 'Reticulating Splines',
            'message_type' => 'text',
            'channel' => 'sms',
            'failover' => [
                [
                    'message_type' => 'text',
                    'to' => '447700900000',
                    'from' => '16105551212',
                    'channel' => 'sms',
                    'text' => 'Reticulating Splines',
                ]
            ]
        ];

        $message = new SMSText($payload['to'], $payload['from'], $payload['text']);
        $failMessage = new SMSText($payload['to'], $payload['from'], $payload['text']);
        $message->addFailover($failMessage);

        $this->httpClient->sendRequest(Argument::that(function (Request $request) use ($payload) {
            $this->assertRequestJsonBodyContains('failover', $payload['failover'], $request);
            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testWillSendValidE164Number()
    {
        $message = new SMSText('447700900000', '16105551212', 'Reticulating Splines');

        $this->httpClient->sendRequest(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('to', '447700900000', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testWillSendValidE164NumberWithPlus()
    {
        $message = new SMSText('+447700900000', '16105551212', 'Reticulating Splines');

        $this->httpClient->sendRequest(Argument::that(function (Request $request) {
            $this->assertRequestJsonBodyContains('to', '447700900000', $request);
            $this->assertEquals('POST', $request->getMethod());

            return true;
        }))->willReturn($this->getResponse('sms-success', 202));
        $result = $this->messageClient->send($message);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message_uuid', $result);
    }

    public function testWillErrorOnInvalidE164Number()
    {
        $this->expectException(\InvalidArgumentException::class);
        $message = new SMSText('00447700900000', '16105551212', 'Reticulating Splines');

        $this->messageClient->send($message);
    }

    public function testWillErrorOnInvalidE164NumberWithPlus()
    {
        $this->expectException(\InvalidArgumentException::class);
        $message = new SMSText('+00447700900000', '16105551212', 'Reticulating Splines');

        $this->messageClient->send($message);
    }
}
