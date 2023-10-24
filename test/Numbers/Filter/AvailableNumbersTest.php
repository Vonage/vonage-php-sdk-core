<?php

namespace VonageTest\Numbers\Filter;

use InvalidArgumentException;
use Vonage\Numbers\Number;
use VonageTest\VonageTestCase;
use Vonage\Numbers\Filter\AvailableNumbers;

class AvailableNumbersTest extends VonageTestCase
{
    /**
     * @dataProvider numberTypes
     */
    public function testCanSetValidNumberType(string $type): void
    {
        $filter = new AvailableNumbers();
        $filter->setType($type);

        $this->assertSame($type, $filter->getType());
    }

    /**
     * List of valid number types that can be searched on
     * 
     * @return array<array<string>>
     */
    public function numberTypes(): array
    {
        return [
            [Number::TYPE_FIXED],
            [Number::TYPE_MOBILE],
            [Number::TYPE_TOLLFREE]
        ];
    }

    public function testInvalidTypeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type of number');

        $filter = new AvailableNumbers();
        $filter->setType('foo-bar');
    }

    public function testCanSetHasApplication(): void
    {
        $filter = new AvailableNumbers();
        $filter->setHasApplication(true);

        $this->assertTrue($filter->getHasApplication());
        $query = $filter->getQuery();
        $this->assertArrayHasKey('has_application', $query);
        $this->assertTrue($query['has_application']);
    }

    public function testCanSetApplicationId(): void
    {
        $filter = new AvailableNumbers();
        $filter->setApplicationId('xxxxxxxx');

        $this->assertSame('xxxxxxxx', $filter->getApplicationId());
        $query = $filter->getQuery();
        $this->assertArrayHasKey('application_id', $query);
        $this->assertSame('xxxxxxxx', $query['application_id']);
    }
}
