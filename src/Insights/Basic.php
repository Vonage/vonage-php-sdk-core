<?php

namespace Nexmo\Insights;

use Nexmo\Entity\Hydrator\ArrayHydrateInterface;

class Basic implements \JsonSerializable, ArrayHydrateInterface
{
    protected $data = [];

    public function __construct($number)
    {
        $this->data['national_format_number'] = $number;
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->data['request_id'];
    }

    /**
     * @return string
     */
    public function getNationalFormatNumber()
    {
        return $this->data['national_format_number'];
    }

    /**
     * @return string
     */
    public function getInternationalFormatNumber()
    {
        return $this->data['international_format_number'];
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->data['country_code'];
    }

    /**
     * @return string
     */
    public function getCountryCodeISO3()
    {
        return $this->data['country_code_iso3'];
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        return $this->data['country_name'];
    }

    /**
     * @return integer
     */
    public function getCountryPrefix()
    {
        return $this->data['country_prefix'];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function fromArray(array $data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
