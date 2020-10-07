<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Voice\Endpoint;

use PHPUnit\Framework\TestCase;
use Vonage\Voice\Endpoint\App;

class AppTest extends TestCase
{
    public function testSetsUsernameAtCreation(): void
    {
        self::assertSame("username", (new App("username"))->getId());
    }

    public function testFactoryCreatesAppEndpoint(): void
    {
        self::assertSame("username", App::factory('username')->getId());
    }

    public function testToArrayHasCorrectStructure(): void
    {
        self::assertSame([
            'type' => 'app',
            'user' => 'username',
        ], (new App("username"))->toArray());
    }

    public function testSerializesToJSONCorrectly(): void
    {
        self::assertSame([
            'type' => 'app',
            'user' => 'username',
        ], (new App("username"))->jsonSerialize());
    }
}
