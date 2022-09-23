<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Voice\NCCO\Action;

use InvalidArgumentException;
use TypeError;
use Vonage\Voice\NCCO\Action\Pay;
use Vonage\Voice\NCCO\NCCO;
use VonageTest\VonageTestCase;
use Vonage\Voice\NCCO\Action\Notify;
use Vonage\Voice\Webhook;

class PayTest extends VonageTestCase
{
    public function testCanCreateFromFactory(): void
    {
        $payAction = Pay::factory(['amount' => 9.99]);

        $this->assertInstanceOf(Pay::class, $payAction);
        $this->assertEquals(9.99, $payAction->getAmount());
    }

    public function testCanAddToNCCO(): void
    {
        $payAction = Pay::factory(['amount' => 9.99]);
        $ncco = new NCCO();
        $ncco->addAction($payAction);
        $output = $ncco->toArray();

        $this->assertEquals('pay', $output[0]['action']);
    }

    public function testThrowsErrorWithNoAmount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Amount is required for this action.');
        $payAction = Pay::factory(['currency' => 'eur']);
    }

    public function testCannotAddAmountAsString(): void
    {
        $this->expectException(TypeError::class);
        $payAction = Pay::factory(['amount' => '9.99']);
    }

    public function testCanAddCurrency(): void
    {
        $payAction = Pay::factory([
            'amount' => 9.99,
            'currency' => 'eur'
        ]);

        $this->assertInstanceOf(Pay::class, $payAction);
        $this->assertEquals('eur', $payAction->getCurrency());
    }

    public function testCannotAddCurrencyAsFloat(): void
    {
        $this->expectException(TypeError::class);

        $payAction = Pay::factory([
            'amount' => 9.99,
            'currency' => 9.99
        ]);
    }

    public function testCannotAddInvalidVoiceSetting()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('type did not fall under permitted voice settings');

        $payAction = Pay::factory([
            'amount' => 9.99,
            'voice' => [
                'type' => 2
            ]
        ]);
    }

    public function testCanGenerateFromFactoryWithVoiceSettings()
    {
        $payAction = Pay::factory([
            'amount' => 9.99,
            'currency' => 'eur',
            'voice' => [
                'language' => 'en_GB',
                'style' => '1'
            ]
        ]);

        $this->assertInstanceOf(Pay::class, $payAction);
        $this->assertEquals('en_GB', $payAction->getVoice()['language']);
        $this->assertEquals('1', $payAction->getVoice()['style']);
    }

    public function testCanAddPrompts(): void
    {
        $payAction = Pay::factory([
            'amount' => 9.99,
            'currency' => 'eur',
            'prompts' => [
                'type' => 'ExpirationDate',
                'text' => 'Please enter expiration date',
                'errors' => [
                    'InvalidExpirationDate' => [
                        'text' => 'Invalid expiration date. Please try again'
                    ],
                    'Timeout' => [
                        'text' => 'Please enter your 4 digit credit card expiration date'
                    ]
                ]
            ]
        ]);

        $this->assertInstanceOf(Pay::class, $payAction);
        $this->assertEquals(
            'Invalid expiration date. Please try again',
            $payAction->getPrompts()['errors']['InvalidExpirationDate']['text']
        );
    }

    public function testCannotAddPromptsWithWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('invalid prompt type');

        $payAction = Pay::factory([
            'amount' => 9.99,
            'currency' => 'eur',
            'prompts' => [
                'type' => 'InvalidCardNumber',
                'text' => 'Please enter a valid card',
                'errors' => [
                    'InvalidSecurityCode' => [
                        'text' => 'Invalid expiration date. Please try again'
                    ],
                ]
            ]
        ]);
    }

    public function testCannotAddPromptWithoutText(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('text is required when setting error text prompts.');

        $payAction = Pay::factory([
            'amount' => 9.99,
            'currency' => 'eur',
            'prompts' => [
                'type' => 'ExpirationDate',
                'text' => 'Please enter expiration date',
                'errors' => [
                    'InvalidExpirationDate' => [
                        'message' => 'Invalid expiration date. Please try again'
                    ],
                    'Timeout' => [
                        'message' => 'Please enter your 4 digit credit card expiration date'
                    ]
                ]
            ]
        ]);
    }

    public function testActionRendersCorrectly(): void
    {
        $data = [
            'amount'   => 9.99,
            'currency' => 'eur',
            'eventUrl' => 'https://myevent.com',
            'prompts'  => [
                'type'   => 'ExpirationDate',
                'text'   => 'Please enter expiration date',
                'errors' => [
                    'InvalidExpirationDate' => [
                        'text' => 'Invalid expiration date. Please try again'
                    ],
                    'Timeout'               => [
                        'text' => 'Please enter your 4 digit credit card expiration date'
                    ]
                ]
            ]
        ];

        $payAction = Pay::factory($data);

        $ncco = new NCCO();
        $ncco->addAction($payAction);

        $renderedPayload = $ncco->toArray();
        $dataWithAction = $data;
        $dataWithAction['action'] = 'pay';
        $this->assertEquals($renderedPayload[0], $dataWithAction);
    }
}
