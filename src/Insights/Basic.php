<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Insights;

use ArrayAccess;
use JsonSerializable;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonUnserializableInterface;

class Basic implements JsonSerializable, JsonUnserializableInterface, ArrayAccess, ArrayHydrateInterface
{
    protected $data = [];

    /**
     * Basic constructor.
     *
     * @param $number
     */
    public function __construct($number)
    {
        $this->data['national_format_number'] = $number;
    }

    /**
     * @return string
     */
    public function getRequestId(): string
    {
        return $this->data['request_id'];
    }

    /**
     * @return string
     */
    public function getNationalFormatNumber(): string
    {
        return $this->data['national_format_number'];
    }

    /**
     * @return string
     */
    public function getInternationalFormatNumber(): string
    {
        return $this->data['international_format_number'];
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->data['country_code'];
    }

    /**
     * @return string
     */
    public function getCountryCodeISO3(): string
    {
        return $this->data['country_code_iso3'];
    }

    /**
     * @return string
     */
    public function getCountryName(): string
    {
        return $this->data['country_name'];
    }

    /**
     * @return integer
     */
    public function getCountryPrefix(): int
    {
        return $this->data['country_prefix'];
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param array $json
     * @return void|null
     */
    public function jsonUnserialize(array $json): void
    {
        trigger_error(
            get_class($this) . "::jsonUnserialize is deprecated, please fromArray() instead",
            E_USER_DEPRECATED
        );

        $this->fromArray($json);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        trigger_error(
            "Array access for " . get_class($this) . " is deprecated, please use getter methods",
            E_USER_DEPRECATED
        );

        return $this->data[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws ClientException
     */
    public function offsetSet($offset, $value): void
    {
        throw new ClientException('Number insights results are read only');
    }

    /**
     * @param mixed $offset
     * @throws ClientException
     */
    public function offsetUnset($offset): void
    {
        throw new ClientException('Number insights results are read only');
    }

    /**
     * @param array $data
     */
    public function fromArray(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
