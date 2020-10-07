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
