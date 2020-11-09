<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\Endpoint\Phone;

class PhoneTest extends TestCase
{
    /**
     * @var string
     */
    protected $number = '15551112323';

    /**
     * @var string
     */
    protected $url = 'https://test.domain/answerNCCO.json';

    /**
     * @var string
     */
    protected $ringbackTone = 'https://test.domain/ringback.mp3';

    /**
     * @var string
     */
    protected $dtmfAnswer = '12';

    /**
     * @var string
     */
    protected $type = 'phone';

    public function testDefaultEndpointIsCreatedProperly(): void
    {
        $endpoint = new Phone($this->number);

        $this->assertSame($this->number, $endpoint->getId());
        $this->assertNull($endpoint->getDtmfAnswer());
        $this->assertNull($endpoint->getRingbackTone());
        $this->assertNull($endpoint->getUrl());
    }

    public function testFactoryCreatesPhoneEndpoint(): void
    {
        $endpoint = Phone::factory($this->number, [
            'dtmfAnswer' => $this->dtmfAnswer,
            'onAnswer' => [
                'url' => $this->url,
                'ringbackTone' => $this->ringbackTone
            ]
        ]);

        $this->assertSame($this->number, $endpoint->getId());
        $this->assertSame($this->url, $endpoint->getUrl());
        $this->assertSame($this->ringbackTone, $endpoint->getRingbackTone());
    }

    public function testFactoryHandlesLegacyRingbackArgument(): void
    {
        $endpoint = Phone::factory($this->number, [
            'dtmfAnswer' => $this->dtmfAnswer,
            'onAnswer' => [
                'url' => $this->url,
                'ringback' => $this->ringbackTone
            ]
        ]);

        $this->assertSame($this->number, $endpoint->getId());
        $this->assertSame($this->url, $endpoint->getUrl());
        $this->assertSame($this->ringbackTone, $endpoint->getRingbackTone());
    }

    public function testToArrayHasCorrectStructure(): void
    {
        $expected = [
            'type' => $this->type,
            'number' => $this->number
        ];

        $this->assertSame($expected, (new Phone($this->number))->toArray());
    }

    public function testRingbackNotReturnedIfURLNotSet(): void
    {
        $expected = [
            'type' => $this->type,
            'number' => $this->number
        ];

        $this->assertSame(
            $expected,
            (new Phone($this->number))->setRingbackTone($this->ringbackTone)->toArray()
        );
    }

    public function testRingbackIsReturnedIfURLIsSet(): void
    {
        $expected = [
            'type' => $this->type,
            'number' => $this->number,
            'onAnswer' => [
                'url' => $this->url,
                'ringbackTone' => $this->ringbackTone
            ]
        ];

        $this->assertSame(
            $expected,
            (new Phone($this->number))
                ->setRingbackTone($this->ringbackTone)
                ->setUrl($this->url)->toArray()
        );
    }

    public function testSerializesToJSONCorrectly(): void
    {
        $expected = [
            'type' => $this->type,
            'number' => $this->number,
            'dtmfAnswer' => $this->dtmfAnswer
        ];

        $endpoint = new Phone($this->number);
        $endpoint->setDtmfAnswer($this->dtmfAnswer);

        $this->assertSame($expected, $endpoint->jsonSerialize());
    }
}
