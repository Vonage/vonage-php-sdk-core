<?php
declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use Vonage\Voice\NCCO\Action\Input;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
{
    public function testSpeechSettingsGenerateCorrectNCCO()
    {
        $action = new Input();
        $action
            ->setSpeechUUID('aaaaaaaa-bbbb-cccc-dddd-0123456789ab')
            ->setSpeechEndOnSilence(5)
            ->setSpeechLanguage('en-US')
            ->setSpeechContext(['foo', 'bar'])
            ->setSpeechStartTimeout(2)
            ->setSpeechMaxDuration(10)
        ;

        $ncco = $action->toNCCOArray();

        $this->assertSame(['aaaaaaaa-bbbb-cccc-dddd-0123456789ab'], $ncco['speech']->uuid);
        $this->assertSame(5, $ncco['speech']->endOnSilence);
        $this->assertSame('en-US', $ncco['speech']->language);
        $this->assertSame(['foo', 'bar'], $ncco['speech']->context);
        $this->assertSame(2, $ncco['speech']->startTimeout);
        $this->assertSame(10, $ncco['speech']->maxDuration);
    }

    public function testSpeechSettingsAreSetInFactory()
    {
        $data = [
            'action' => 'input',
            'speech' => [
                'uuid' => ['aaaaaaaa-bbbb-cccc-dddd-0123456789ab'],
                'endOnSilence' => '5',
                'language' => 'en-US',
                'context' => ['foo', 'bar'],
                'startTimeout' => '2',
                'maxDuration' => '10'
            ]
        ];

        $action = Input::factory($data);

        $this->assertSame('aaaaaaaa-bbbb-cccc-dddd-0123456789ab', $action->getSpeechUUID());
        $this->assertSame(5, $action->getSpeechEndOnSilence());
        $this->assertSame('en-US', $action->getSpeechLanguage());
        $this->assertSame(['foo', 'bar'], $action->getSpeechContext());
        $this->assertSame(2, $action->getSpeechStartTimeout());
        $this->assertSame(10, $action->getSpeechMaxDuration());
    }

    public function testDTMFSettingsGenerateCorrectNCCO()
    {
        $action = new Input();
        $action
            ->setDtmfMaxDigits(2)
            ->setDtmfSubmitOnHash(true)
            ->setDtmfTimeout(5)
        ;

        $ncco = $action->toNCCOArray();

        $this->assertSame(2, $ncco['dtmf']->maxDigits);
        $this->assertSame('true', $ncco['dtmf']->submitOnHash);
        $this->assertSame(5, $ncco['dtmf']->timeOut);
    }

    public function testDTMFSettingsAreSetInFactory()
    {
        $data = [
            'action' => 'input',
            'dtmf' => [
                'timeOut' => '2',
                'maxDigits' => '5',
                'submitOnHash' => 'false',
            ]
        ];

        $action = Input::factory($data);

        $this->assertSame(5, $action->getDtmfMaxDigits());
        $this->assertSame(2, $action->getDtmfTimeout());
        $this->assertSame(false, $action->getDtmfSubmitOnHash());
    }

    public function testEventURLCanBeSetInFactory()
    {
        $data = [
            'action' => 'input',
            'eventUrl' => ['https://test.domain/events'],
            'eventMethod' => 'POST',
            'speech' => [],
        ];

        $action = Input::factory($data);
        $ncco = $action->toNCCOArray();

        $this->assertSame($data['eventUrl'], $ncco['eventUrl']);
        $this->assertSame($data['eventMethod'], $ncco['eventMethod']);

        $this->assertSame($data['eventUrl'][0], $action->getEventWebhook()->getUrl());
        $this->assertSame($data['eventMethod'], $action->getEventWebhook()->getMethod());
    }

    public function testEventMethodDefaultsToPostWhenNotSupplied()
    {
        $data = [
            'action' => 'input',
            'eventUrl' => ['https://test.domain/events'],
            'dtmf' => []
        ];

        $action = Input::factory($data);
        $ncco = $action->toNCCOArray();

        $this->assertSame($data['eventUrl'], $ncco['eventUrl']);
        $this->assertSame('POST', $ncco['eventMethod']);

        $this->assertSame($data['eventUrl'][0], $action->getEventWebhook()->getUrl());
        $this->assertSame('POST', $action->getEventWebhook()->getMethod());
    }

    public function testJSONSerializationLooksCorrect()
    {
        $expected = [
            'action' => 'input',
            'dtmf' => (object) []
        ];

        $action = new Input();
        $action->setEnableDtmf(true);

        $this->assertEquals($expected, $action->jsonSerialize());
    }

    public function testThrowsRuntimeExceptionIfNoInputDefined()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Input NCCO action must have either speech or DTMF enabled');

        $action = new Input();
        $action->toNCCOArray();
    }
}
