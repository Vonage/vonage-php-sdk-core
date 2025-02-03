<?php

declare(strict_types=1);

namespace VonageTest\Verify2\VerifyObjects;

use PHPUnit\Framework\TestCase;
use Vonage\Verify2\VerifyObjects\Template;

class TemplateTest extends TestCase
{
    public function testConstructorInitializesData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $template = new Template($data);

        $this->assertSame($data, $template->toArray());
    }

    public function testPropertyGetAndSet()
    {
        $template = new Template();
        $template->key1 = 'value1';

        $this->assertSame('value1', $template->key1);
        $this->assertNull($template->key2);
    }

    public function testPropertyIsset()
    {
        $template = new Template(['key1' => 'value1']);

        $this->assertTrue(isset($template->key1));
        $this->assertFalse(isset($template->key2));
    }

    public function testFromArrayHydratesData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $template = new Template();
        $template->fromArray($data);

        $this->assertSame($data, $template->toArray());
    }

    public function testToArrayReturnsData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $template = new Template($data);

        $this->assertSame($data, $template->toArray());
    }

    public function testChainingWhenSettingProperties()
    {
        $template = new Template();
        $result = $template->__set('key1', 'value1');

        $this->assertInstanceOf(Template::class, $result);
    }
}
