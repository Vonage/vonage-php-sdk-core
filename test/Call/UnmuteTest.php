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
use Vonage\Call\Unmute;

class UnmuteTest extends TestCase
{
    use JsonAssertions;

    public function testStructure(): void
    {
        $schema = file_get_contents(__DIR__ . '/schema/unmute.json');
        $json = json_decode(json_encode(@new Unmute()), true);

        self::assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
        self::assertJsonValueEquals($json, '$.action', 'unmute');
    }
}
