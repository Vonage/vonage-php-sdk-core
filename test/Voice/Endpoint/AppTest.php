<?php

declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use VonageTest\VonageTestCase;
use Vonage\Voice\Endpoint\App;

class AppTest extends VonageTestCase
{
    public function testSetsUsernameAtCreation(): void
    {
        $this->assertSame("username", (new App("username"))->getId());
    }

    public function testFactoryCreatesAppEndpoint(): void
    {
        $this->assertSame("username", App::factory('username')->getId());
    }

    public function testToArrayHasCorrectStructure(): void
    {
        $this->assertSame([
            'type' => 'app',
            'user' => 'username',
        ], (new App("username"))->toArray());
    }

    public function testSerializesToJSONCorrectly(): void
    {
        $this->assertSame([
            'type' => 'app',
            'user' => 'username',
        ], (new App("username"))->jsonSerialize());
    }
}
