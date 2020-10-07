<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Test\Call;

use PHPUnit\Framework\TestCase;
use Vonage\Call\Event;
use Vonage\Test\Fixture\ResponseTrait;

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
        self::assertSame('5dd627ff-caff-46a8-99ed-891e5ffebc55', $this->entity->getId());
        self::assertSame('5dd627ff-caff-46a8-99ed-891e5ffebc55', $this->entity['uuid']);
    }

    public function testGetMessage(): void
    {
        self::assertSame('Stream stopped', $this->entity->getMessage());
        self::assertSame('Stream stopped', $this->entity['message']);
    }
}
