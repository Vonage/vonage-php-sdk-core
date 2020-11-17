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
use Vonage\Call\Mute;

use function file_get_contents;
use function json_decode;
use function json_encode;

class MuteTest extends TestCase
{
    use JsonAssertions;

    public function testStructure(): void
    {
        $schema = file_get_contents(__DIR__ . '/schema/mute.json');
        $json = json_decode(json_encode(@new Mute()), true);

        $this->assertJsonDocumentMatchesSchema($json, json_decode(json_encode($schema), true));
        $this->assertJsonValueEquals($json, '$.action', 'mute');
    }
}
