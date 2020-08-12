<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace VonageTest;

use Vonage\Message\Message;
use GuzzleHttp\Psr7\Request;
use Vonage\Message\MessageInterface;

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
