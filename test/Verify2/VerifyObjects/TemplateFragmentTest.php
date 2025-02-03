<?php

declare(strict_types=1);

namespace VonageTest\Verify2\VerifyObjects;

use PHPUnit\Framework\TestCase;
use Vonage\Verify2\VerifyObjects\TemplateFragment;

class TemplateFragmentTest extends TestCase
{
    public function testConstructorInitializesData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $templateFragment = new TemplateFragment($data);

        $this->assertSame($data, $templateFragment->toArray());
    }

    public function testPropertyGetAndSet()
    {
        $templateFragment = new TemplateFragment();
        $templateFragment->key1 = 'value1';

        $this->assertSame('value1', $templateFragment->key1);
        $this->assertNull($templateFragment->key2);
    }

    public function testPropertyIsset()
    {
        $templateFragment = new TemplateFragment(['key1' => 'value1']);

        $this->assertTrue(isset($templateFragment->key1));
        $this->assertFalse(isset($templateFragment->key2));
    }

    public function testFromArrayHydratesData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $templateFragment = new TemplateFragment();
        $templateFragment->fromArray($data);

        $this->assertSame($data, $templateFragment->toArray());
    }

    public function testToArrayReturnsData()
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];
        $templateFragment = new TemplateFragment($data);

        $this->assertSame($data, $templateFragment->toArray());
    }

    public function testChainingWhenSettingProperties()
    {
        $templateFragment = new TemplateFragment();
        $result = $templateFragment->__set('key1', 'value1');

        $this->assertInstanceOf(TemplateFragment::class, $result);
    }
}
