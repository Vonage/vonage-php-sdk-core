<?php

namespace VonageTest\Numbers\Filter;

use InvalidArgumentException;
use Vonage\Numbers\Number;
use PHPUnit\Framework\TestCase;
use Vonage\Numbers\Filter\AvailableNumbers;

class AvailableNumbersTest extends TestCase
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
}
