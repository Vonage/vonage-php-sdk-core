<?php

namespace VonageTest\Subaccount\Filter;

use PHPUnit\Framework\TestCase;
use Vonage\Client\Exception\Request;
use Vonage\Subaccount\Filter\SubaccountFilter;

class SubaccountTest extends TestCase
{
    public function testGetQuery(): void
    {
        $filterValues = [
            'start_date' => '2023-01-01',
            'end_date' => '2023-06-30',
            'subaccount' => 'my_subaccount'
        ];

        $subaccountFilter = new SubaccountFilter($filterValues);
        $expectedQuery = [
            'start_date' => '2023-01-01',
            'end_date' => '2023-06-30',
            'subaccount' => 'my_subaccount'
        ];

        $this->assertEquals($expectedQuery, $subaccountFilter->getQuery());
    }

    public function testWillDefaultStartDate(): void
    {
        $subaccountFilter = new SubaccountFilter([]);
        $today = date('Y-m-d');
        $this->assertEquals($today, $subaccountFilter->getStartDate());
    }

    public function testGetStartDate(): void
    {
        $filterValues = [
            'start_date' => '2023-01-01'
        ];

        $subaccountFilter = new SubaccountFilter($filterValues);
        $this->assertEquals('2023-01-01', $subaccountFilter->getStartDate());
    }

    public function testSetStartDate(): void
    {
        $subaccountFilter = new SubaccountFilter([]);
        $subaccountFilter->setStartDate('2023-01-01');
        $this->assertEquals('2023-01-01', $subaccountFilter->getStartDate());
    }

    public function testGetEndDate(): void
    {
        $filterValues = [
            'end_date' => '2023-06-30'
        ];

        $subaccountFilter = new SubaccountFilter($filterValues);
        $this->assertEquals('2023-06-30', $subaccountFilter->getEndDate());
    }

    public function testSetEndDate(): void
    {
        $subaccountFilter = new SubaccountFilter([]);
        $subaccountFilter->setEndDate('2023-06-30');
        $this->assertEquals('2023-06-30', $subaccountFilter->getEndDate());
    }

    public function testGetSubaccount(): void
    {
        $filterValues = [
            'subaccount' => 'my_subaccount'
        ];

        $subaccountFilter = new SubaccountFilter($filterValues);
        $this->assertEquals('my_subaccount', $subaccountFilter->getSubaccount());
    }

    public function testSetSubaccount(): void
    {
        $subaccountFilter = new SubaccountFilter([]);
        $subaccountFilter->setSubaccount('my_subaccount');
        $this->assertEquals('my_subaccount', $subaccountFilter->getSubaccount());
    }

    public function testConstructionWithValidValues(): void
    {
        $filterValues = [
            'start_date' => '2023-01-01',
            'end_date'   => '2023-06-30',
            'subaccount' => 'my_subaccount'
        ];

        $subaccountFilter = new SubaccountFilter($filterValues);

        $this->assertInstanceOf(SubaccountFilter::class, $subaccountFilter);
    }

    public function testConstructionWithInvalidKeyThrowsException(): void
    {
        $this->expectException(Request::class);

        $filterValues = [
            'start_date'  => '2023-01-01',
            'end_date'    => '2023-06-30',
            'invalid_key' => 'value'
        ];

        new SubaccountFilter($filterValues);
    }

    public function testConstructionWithNonStringValueThrowsException(): void
    {
        $this->expectException(Request::class);

        $filterValues = [
            'start_date' => '2023-01-01',
            'end_date'   => '2023-06-30',
            'subaccount' => 12345
        ];

        new SubaccountFilter($filterValues);
    }
}
