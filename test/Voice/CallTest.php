<?php
declare(strict_types=1);

namespace VonageTest\Voice;

use Vonage\Voice\Call;
use PHPUnit\Framework\TestCase;

class CallTest extends TestCase
{
    public function testConvertsToArrayProperly()
    {
        $data = json_decode(file_get_contents(__DIR__ . '/responses/call.json'), true);
        $call = new Call($data);
        $callData = $call->toArray();

        $this->assertEquals($data['uuid'], $callData['uuid']);
        $this->assertEquals($data['status'], $callData['status']);
        $this->assertEquals($data['direction'], $callData['direction']);
        $this->assertEquals($data['rate'], $callData['rate']);
        $this->assertEquals($data['price'], $callData['price']);
        $this->assertEquals($data['duration'], $callData['duration']);
        $this->assertEquals($data['start_time'], $callData['start_time']);
        $this->assertEquals($data['end_time'], $callData['end_time']);
        $this->assertEquals($data['network'], $callData['network']);

        $this->assertEquals($data['to'][0]['type'], $callData['to'][0]['type']);
        $this->assertEquals($data['to'][0]['number'], $callData['to'][0]['number']);
        $this->assertEquals($data['from'][0]['type'], $callData['from'][0]['type']);
        $this->assertEquals($data['from'][0]['number'], $callData['from'][0]['number']);
    }
}
