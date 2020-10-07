<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Call;

use Helmich\JsonAssert\JsonAssertions;
use PHPUnit\Framework\TestCase;
use Vonage\Call\Transfer;

class TransferTest extends TestCase
{
    use JsonAssertions;

    public function testStructureWithArray(): void
    {
        $urls = ['http://example.com', 'http://alternate.example.com'];
        $schema = file_get_contents(__DIR__ . '/schema/transfer.json');
        $json = json_decode(json_encode(@new Transfer($urls)), true);

        self::assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
        self::assertJsonValueEquals($json, '$.action', 'transfer');
        self::assertJsonValueEquals($json, '$.destination.type', 'ncco');
        self::assertJsonValueEquals($json, '$.destination.url', $urls);
    }

    public function testStructureWithString(): void
    {
        $urls = 'http://example.com';
        $schema = file_get_contents(__DIR__ . '/schema/transfer.json');
        $json = json_decode(json_encode(@new Transfer($urls)), true);

        self::assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
        self::assertJsonValueEquals($json, '$.action', 'transfer');
        self::assertJsonValueEquals($json, '$.destination.type', 'ncco');
        self::assertJsonValueEquals($json, '$.destination.url', [$urls]);
    }
}
