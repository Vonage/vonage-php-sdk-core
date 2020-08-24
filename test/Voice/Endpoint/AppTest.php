<?php
declare(strict_types=1);

namespace VonageTest\Voice\Endpoint;

use Vonage\Voice\Endpoint\App;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    public function testSetsUsernameAtCreation()
    {
        $endpoint = new App("username");
        $this->assertSame("username", $endpoint->getId());
    }

    public function testFactoryCreatesAppEndpoint()
    {
        $endpoint = App::factory('username');
        $this->assertSame("username", $endpoint->getId());
    }

    public function testToArrayHasCorrectStructure()
    {
        $expected = [
            'type' => 'app',
            'user' => 'username',
        ];
        
        $endpoint = new App("username");
        $this->assertSame($expected, $endpoint->toArray());
    }

    public function testSerializesToJSONCorrectly()
    {
        $expected = [
            'type' => 'app',
            'user' => 'username',
        ];
        
        $endpoint = new App("username");
        $this->assertSame($expected, $endpoint->jsonSerialize());
    }
}
