<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Call;

use JsonSerializable;
use RuntimeException;

/**
 * Class Endpoint
 * Represents a call destination / origin.
 *
 * TODO: Try to unify this and other (message, etc) endpoint identifiers.
 *
 * @deprecated Please use Vonage\Voice\Endpoint\Phone instead
 */
class Endpoint implements JsonSerializable
{
    public const PHONE = 'phone';

    protected $id;

    protected $type;

    protected $additional;

    /**
     * Endpoint constructor.
     *
     * @param $id
     * @param string $type
     * @param array $additional
     */
    public function __construct($id, $type = self::PHONE, $additional = [])
    {
        trigger_error(
            'Vonage\Call\Endpoint is deprecated, please use Vonage\Voice\Endpoint\Phone instead',
            E_USER_DEPRECATED
        );

        $this->id = $id;
        $this->type = $type;
        $this->additional = $additional;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $property
     * @param $value
     * @return $this
     */
    public function set($property, $value): self
    {
        $this->additional[$property] = $value;

        return $this;
    }

    /**
     * @param $property
     * @return mixed
     */
    public function get($property)
    {
        return $this->additional[$property] ?? null;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        if (!self::PHONE === $this->type) {
            throw new RuntimeException('number not defined for this type');
        }

        return $this->getId();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getId();
    }

    /**
     * @return array|null
     */
    public function jsonSerialize(): ?array
    {
        if ($this->type === 'phone') {
            return array_merge(
                $this->additional,
                [
                    'type' => $this->type,
                    'number' => $this->id
                ]
            );
        }

        throw new RuntimeException('unknown type: ' . $this->type);
    }
}
