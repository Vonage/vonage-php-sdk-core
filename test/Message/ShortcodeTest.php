<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Message;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Message\Shortcode;
use Vonage\Message\Shortcode\Alert;
use Vonage\Message\Shortcode\Marketing;
use Vonage\Message\Shortcode\TwoFactor;

class ShortcodeTest extends TestCase
{
    /**
     * @dataProvider typeProvider
     *
     * @param $klass
     * @param $expectedType
     */
    public function testType($klass, $expectedType): void
    {
        $m = new $klass('14155550100');

        $this->assertEquals($expectedType, $m->getType());
    }

    /**
     * @dataProvider typeProvider
     *
     * @param $expected
     * @param $type
     *
     * @throws ClientException
     */
    public function testCreateMessageFromArray($expected, $type): void
    {
        $message = Shortcode::createMessageFromArray(['type' => $type, 'to' => '14155550100']);
        $this->assertInstanceOf($expected, $message);
    }

    /**
     * @return string[]
     */
    public function typeProvider(): array
    {
        return [
            [TwoFactor::class, '2fa'],
            [Marketing::class, 'marketing'],
            [Alert::class, 'alert']
        ];
    }

    public function testGetRequestData(): void
    {
        $m = new TwoFactor("14155550100", ['link' => 'https://example.com'], ['status-report-req' => 1]);
        $actual = $m->getRequestData();

        $this->assertEquals([
            'to' => '14155550100',
            'link' => 'https://example.com',
            'status-report-req' => 1
        ], $actual);
    }
}
