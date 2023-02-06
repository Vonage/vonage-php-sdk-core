<?php

declare(strict_types=1);

namespace VonageTest;

use InvalidArgumentException;
use Vonage\Message\MessageInterface;

use function count;

trait MessageAssertionTrait
{
    public static function assertListOfMessagesEqual(array $expected, array $actual): void
    {
        $expectedCount = count($expected);
        $actualCount = count($actual);

        if ($expectedCount !== $actualCount) {
            throw new InvalidArgumentException('Expected count and actual count must match');
        }

        // If passed empty arrays, there are no messages to compare
        if ($expectedCount === 0) {
            return;
        }

        foreach ($expected as $k => $item) {
            self::assertMessagesEqual($expected[$k], $actual[$k]);
        }
    }

    public static function assertMessagesEqual(MessageInterface $expected, MessageInterface $actual): void
    {
        self::assertEquals($expected->getResponseData(), $actual->getResponseData());
    }
}
