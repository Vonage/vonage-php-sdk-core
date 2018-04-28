<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Message;
use Nexmo\Message\Shortcode;
use Nexmo\Message\Shortcode\TwoFactor;
use Nexmo\Message\Shortcode\Marketing;
use Nexmo\Message\Shortcode\Alert;

class ShortcodeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function tearDown()
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
