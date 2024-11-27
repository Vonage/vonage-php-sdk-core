<?php

declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use VonageTest\VonageTestCase;
use RuntimeException;
use Vonage\Voice\NCCO\Action\Input;

class InputTest extends VonageTestCase
{
    public function testSpeechSettingsGenerateCorrectNCCO(): void
    {
        $ncco = (new Input())
            ->setSpeechUUID('aaaaaaaa-bbbb-cccc-dddd-0123456789ab')
            ->setSpeechEndOnSilence(5)
            ->setSpeechLanguage('en-US')
            ->setSpeechContext(['foo', 'bar'])
            ->setSpeechStartTimeout(2)
            ->setSpeechMaxDuration(10)
            ->toNCCOArray();

        $this->assertSame(['aaaaaaaa-bbbb-cccc-dddd-0123456789ab'], $ncco['speech']->uuid);
        $this->assertSame(5, $ncco['speech']->endOnSilence);
        $this->assertSame('en-US', $ncco['speech']->language);
        $this->assertSame(['foo', 'bar'], $ncco['speech']->context);
        $this->assertSame(2, $ncco['speech']->startTimeout);
        $this->assertSame(10, $ncco['speech']->maxDuration);
    }

    public function testSpeechSettingsAreSetInFactory(): void
    {
        $action = Input::factory([
            'action' => 'input',
            'speech' => [
                'uuid' => ['aaaaaaaa-bbbb-cccc-dddd-0123456789ab'],
                'endOnSilence' => '5',
                'language' => 'en-US',
                'context' => ['foo', 'bar'],
                'startTimeout' => '2',
                'maxDuration' => '10'
            ]
        ]);

        $this->assertSame('aaaaaaaa-bbbb-cccc-dddd-0123456789ab', $action->getSpeechUUID());
        $this->assertSame(5, $action->getSpeechEndOnSilence());
        $this->assertSame('en-US', $action->getSpeechLanguage());
        $this->assertSame(['foo', 'bar'], $action->getSpeechContext());
        $this->assertSame(2, $action->getSpeechStartTimeout());
        $this->assertSame(10, $action->getSpeechMaxDuration());
    }

    public function testDTMFSettingsGenerateCorrectNCCO(): void
    {
        $ncco = (new Input())
            ->setDtmfMaxDigits(2)
            ->setDtmfSubmitOnHash(true)
            ->setDtmfTimeout(5)
            ->toNCCOArray();

        $this->assertSame(2, $ncco['dtmf']->maxDigits);
        $this->assertSame('true', $ncco['dtmf']->submitOnHash);
        $this->assertSame(5, $ncco['dtmf']->timeOut);
    }

    public function testDTMFSettingsAreSetInFactory(): void
    {
        $action = Input::factory([
            'action' => 'input',
            'dtmf' => [
                'timeOut' => '2',
                'maxDigits' => '5',
                'submitOnHash' => 'false',
            ]
        ]);

        $this->assertSame(5, $action->getDtmfMaxDigits());
        $this->assertSame(2, $action->getDtmfTimeout());
        $this->assertFalse($action->getDtmfSubmitOnHash());
    }

    public function testEventURLCanBeSetInFactory(): void
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

    public function testEventMethodDefaultsToPostWhenNotSupplied(): void
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

    public function testJSONSerializationLooksCorrect(): void
    {
        $this->assertEquals([
            'action' => 'input',
            'dtmf' => (object)[]
        ], (new Input())->setEnableDtmf(true)->jsonSerialize());
    }

    public function testThrowsRuntimeExceptionIfNoInputDefined(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Input NCCO action must have either speech or DTMF enabled');

        $input = new Input();
        $array = $input->toNCCOArray();
    }

    public function testCanCreateInputSyncNCCOCorrectly(): void
    {
        $data = [
            'action' => 'input',
            'eventUrl' => ['https://test.domain/events'],
            'dtmf' => [
                'maxDigits' => 4,
            ],
            'mode' => 'synchronous'
        ];

        $action = Input::factory($data);
        $ncco = $action->toNCCOArray();

        $this->assertSame($data['dtmf']['maxDigits'], $action->getDtmfMaxDigits());
        $this->assertSame($data['dtmf']['maxDigits'], $ncco['dtmf']->maxDigits);
        $this->assertSame($data['mode'], $ncco['mode']);
        $this->assertSame('POST', $action->getEventWebhook()->getMethod());
        $this->assertSame($data['mode'], $action->getMode());
    }

    public function testCanCreateInputAsyncNCCOCorrectly(): void
    {
        $data = [
            'action' => 'input',
            'eventUrl' => ['https://test.domain/events'],
            'mode' => 'asynchronous'
        ];

        $action = Input::factory($data);
        $ncco = $action->toNCCOArray();

        $this->assertSame($data['mode'], $ncco['mode']);
        $this->assertSame('POST', $action->getEventWebhook()->getMethod());
        $this->assertSame($data['mode'], $action->getMode());
    }

    public function testCannotCreateInputNCCOWithDtmfAndAsyncMode(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = [
            'action' => 'input',
            'eventUrl' => ['https://test.domain/events'],
            'dtmf' => [
                'maxDigits' => 4,
            ],
            'mode' => 'asynchronous'
        ];

        $action = Input::factory($data);
    }

    public function testErrorsOnInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = [
            'action' => 'input',
            'eventUrl' => ['https://test.domain/events'],
            'dtmf' => [
                'maxDigits' => 4,
            ],
            'mode' => 'syncronus'
        ];

        $action = Input::factory($data);
    }
}
