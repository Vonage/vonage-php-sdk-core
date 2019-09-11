<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest;

use Nexmo\Message\Message;
use GuzzleHttp\Psr7\Request;
use Nexmo\Message\MessageInterface;

trait MessageAssertionTrait
{
    public static function assertListOfMessagesEqual(array $expected, array $actual)
    {
        $expectedCount = count($expected);
        $actualCount = count($actual);

        if ($expectedCount !== $actualCount) {
            throw new \InvalidArgumentException('Expected count and actual count must match');
        }

        // If passed empty arrays, there are no messages to compare
        if ($expectedCount === 0) {
            return;
        }

        foreach ($expected as $k => $item) {
            self::assertMessagesEqual($expected[$k], $actual[$k]);
        }
    }

    public static function assertMessagesEqual(MessageInterface $expected, MessageInterface $actual)
    {
        self::assertEquals($expected->getResponseData(), $actual->getResponseData());
    }
}
