<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Call;

use PHPUnit\Framework\TestCase;
use Vonage\Call\Event;
use VonageTest\Fixture\ResponseTrait;

class EventTest extends TestCase
{
    use ResponseTrait;

    protected $entity;

    public function setup(): void
    {
        $data = $this->getResponseData(['calls', 'event']);
        $this->entity = @new Event($data);
    }

    public function testExpectsMessage(): void
    {
        $this->expectException('InvalidArgumentException');
        @new Event(['uuid' => 'something_unique']);
    }

    public function testExpectsUUID(): void
    {
        $this->expectException('InvalidArgumentException');
        @new Event(['message' => 'something happened']);
    }

    public function testGetId(): void
    {
        $this->assertSame('5dd627ff-caff-46a8-99ed-891e5ffebc55', $this->entity->getId());
        $this->assertSame('5dd627ff-caff-46a8-99ed-891e5ffebc55', $this->entity['uuid']);
    }

    public function testGetMessage(): void
    {
        $this->assertSame('Stream stopped', $this->entity->getMessage());
        $this->assertSame('Stream stopped', $this->entity['message']);
    }
}
