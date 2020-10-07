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
use Vonage\Call\Mute;

class MuteTest extends TestCase
{
    use JsonAssertions;

    public function testStructure(): void
    {
        $schema = file_get_contents(__DIR__ . '/schema/mute.json');
        $json = json_decode(json_encode(@new Mute()), true);

        self::assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
        self::assertJsonValueEquals($json, '$.action', 'mute');
    }
}
