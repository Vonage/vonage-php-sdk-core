<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2017 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace NexmoTest\Call;

use Nexmo\Call\Event;
use NexmoTest\Fixture\ResponseTrait;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    use ResponseTrait;

    protected $entity;

    public function setup()
    {
        $data = $this->getResponseData(['calls', 'event']);
        $this->entity = @new Event($data);
    }

    public function testExpectsMessage()
    {
        $this->expectException('InvalidArgumentException');
        @new Event(['uuid' => 'something_unique']);
    }

    public function testExpectsUUID()
    {
        $this->expectException('InvalidArgumentException');
        @new Event(['message' => 'something happened']);
    }

    public function testGetId()
    {
        $this->assertSame('5dd627ff-caff-46a8-99ed-891e5ffebc55', $this->entity->getId());
        $this->assertSame('5dd627ff-caff-46a8-99ed-891e5ffebc55', $this->entity['uuid']);
    }

    public function testGetMessage()
    {
        $this->assertSame('Stream stopped', $this->entity->getMessage());
        $this->assertSame('Stream stopped', $this->entity['message']);
    }
}
