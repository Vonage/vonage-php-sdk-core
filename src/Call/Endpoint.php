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

use function array_merge;
use function trigger_error;

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

    /**
     * @var string|null
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $additional;

    public function __construct(?string $id, string $type = self::PHONE, array $additional = [])
    {
        trigger_error(
            'Vonage\Call\Endpoint is deprecated, please use Vonage\Voice\Endpoint\Phone instead',
            E_USER_DEPRECATED
        );

        $this->id = $id;
        $this->type = $type;
        $this->additional = $additional;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function set($property, $value): self
    {
        $this->additional[$property] = $value;

        return $this;
    }

    public function get($property)
    {
        return $this->additional[$property] ?? null;
    }

    public function getNumber(): ?string
    {
        if (!self::PHONE === $this->type) {
            throw new RuntimeException('number not defined for this type');
        }

        return $this->getId();
    }

    public function __toString(): string
    {
        return (string)$this->getId();
    }

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
