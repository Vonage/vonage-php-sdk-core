<?php

namespace Vonage\Subaccount\Filter;

use Vonage\Client\Exception\Request;
use Vonage\Entity\Filter\FilterInterface;

class SubaccountFilter implements FilterInterface
{
    public string $startDate = '';
    public ?string $endDate = null;
    public ?string $subaccount = null;

    public static array $possibleParameters = [
        'start_date',
        'end_date',
        'subaccount'
    ];

    public function __construct(array $filterValues)
    {
        foreach ($filterValues as $key => $value) {
            if (! in_array($key, self::$possibleParameters, true)) {
                throw new Request($value . ' is not a valid value');
            }

            if (!is_string($value)) {
                throw new Request($value . ' is not a string');
            }
        }

        if (array_key_exists('start_date', $filterValues)) {
            $this->setStartDate($filterValues['start_date']);
        }

        if ($this->startDate === '') {
            $this->startDate = date('Y-m-d');
        }

        if (array_key_exists('end_date', $filterValues)) {
            $this->setEndDate($filterValues['end_date']);
        }

        if (array_key_exists('subaccount', $filterValues)) {
            $this->setSubaccount($filterValues['subaccount']);
        }
    }

    public function getQuery()
    {
        $data = [];

        if ($this->getStartDate()) {
            $data['start_date'] = $this->getStartDate();
        }

        if ($this->getEndDate()) {
            $data['end_date'] = $this->getEndDate();
        }

        if ($this->getSubaccount()) {
            $data['subaccount'] = $this->getSubaccount();
        }

        return $data;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(?string $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getSubaccount(): ?string
    {
        return $this->subaccount;
    }

    public function setSubaccount(?string $subaccount): void
    {
        $this->subaccount = $subaccount;
    }
}