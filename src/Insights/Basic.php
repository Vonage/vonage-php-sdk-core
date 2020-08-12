<?php

namespace Vonage\Insights;

use Vonage\Client\Exception\Exception;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonUnserializableInterface;

class Basic implements \JsonSerializable, JsonUnserializableInterface, \ArrayAccess, ArrayHydrateInterface
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

    public function jsonUnserialize(array $json)
    {
        trigger_error(
            get_class($this) . "::jsonUnserialize is deprecated, please fromArray() instead",
            E_USER_DEPRECATED
        );

        $this->fromArray($json);
    }

    public function offsetExists($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception('Number insights results are read only');
    }

    public function offsetUnset($offset)
    {
        throw new Exception('Number insights results are read only');
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
