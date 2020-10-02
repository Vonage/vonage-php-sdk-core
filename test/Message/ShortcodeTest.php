<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest\Message;
use Vonage\Message\Shortcode;
use Vonage\Message\Shortcode\TwoFactor;
use Vonage\Message\Shortcode\Marketing;
use Vonage\Message\Shortcode\Alert;
use PHPUnit\Framework\TestCase;

class ShortcodeTest extends TestCase
{
    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    /**
     * @dataProvider typeProvider
     */
    public function testType($klass, $expectedType)
    {
        $m = new $klass('14155550100');
        $this->assertEquals($expectedType, $m->getType());
    }

    /**
     * @dataProvider typeProvider
     */
    public function testCreateMessageFromArray($expected, $type)
    {
        $message = Shortcode::createMessageFromArray(['type' => $type, 'to' => '14155550100']);
        $this->assertInstanceOf($expected, $message);
    }

    public function typeProvider()
    {
        return [
            [TwoFactor::class, '2fa'],
            [Marketing::class, 'marketing'],
            [Alert::class, 'alert']
        ];
    }

    public function testGetRequestData()
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
