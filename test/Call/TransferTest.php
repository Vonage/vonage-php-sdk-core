<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Call;

use Helmich\JsonAssert\JsonAssertions;
use PHPUnit\Framework\TestCase;
use Vonage\Call\Transfer;

use function file_get_contents;
use function json_decode;
use function json_encode;

class TransferTest extends TestCase
{
    use JsonAssertions;

    public function testStructureWithArray(): void
    {
        $urls = ['http://example.com', 'http://alternate.example.com'];
        $schema = file_get_contents(__DIR__ . '/schema/transfer.json');
        $json = json_decode(json_encode(@new Transfer($urls)), true);

        $this->assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
        $this->assertJsonValueEquals($json, '$.action', 'transfer');
        $this->assertJsonValueEquals($json, '$.destination.type', 'ncco');
        $this->assertJsonValueEquals($json, '$.destination.url', $urls);
    }

    public function testStructureWithString(): void
    {
        $urls = 'http://example.com';
        $schema = file_get_contents(__DIR__ . '/schema/transfer.json');
        $json = json_decode(json_encode(@new Transfer($urls)), true);

        $this->assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
        $this->assertJsonValueEquals($json, '$.action', 'transfer');
        $this->assertJsonValueEquals($json, '$.destination.type', 'ncco');
        $this->assertJsonValueEquals($json, '$.destination.url', [$urls]);
    }
}
