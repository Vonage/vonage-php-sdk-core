<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Insights;

use Vonage\Entity\Hydrator\ArrayHydrateInterface;

class Basic implements ArrayHydrateInterface
{
    protected array $data = [];

    /**
     * @param $number
     */
    public function __construct($number)
    {
        $this->data['national_format_number'] = $number;
    }

    public function getRequestId(): string
    {
        return $this->data['request_id'];
    }

    public function getNationalFormatNumber(): string
    {
        return $this->data['national_format_number'];
    }

    public function getInternationalFormatNumber(): string
    {
        return $this->data['international_format_number'];
    }

    public function getCountryCode(): string
    {
        return $this->data['country_code'];
    }

    public function getCountryCodeISO3(): string
    {
        return $this->data['country_code_iso3'];
    }

    public function getCountryName(): string
    {
        return $this->data['country_name'];
    }

    public function getCountryPrefix(): string
    {
        return $this->data['country_prefix'];
    }

    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
