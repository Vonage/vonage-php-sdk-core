<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Call;

/**
 * Class Endpoint
 * Represents a call destination / origin.
 *
 * TODO: Try to unify this and other (message, etc) endpoint identifiers.
 *
 * @deprecated Please use Vonage\Voice\Endpoint\Phone instead
 */
class Endpoint implements \JsonSerializable
{
    const PHONE = 'phone';

    protected $id;

    protected $type;

    protected $additional;

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

    public function getType()
    {
        return $this->type;
    }

    public function getId()
    {
        return $this->id;
    }

    public function set($property, $value)
    {
        $this->additional[$property] = $value;
        return $this;
    }

    public function get($property)
    {
        if (isset($this->additional[$property])) {
            return $this->additional[$property];
        }
    }

    public function getNumber()
    {
        if (!self::PHONE == $this->type) {
            throw new \RuntimeException('number not defined for this type');
        }

        return $this->getId();
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function jsonSerialize()
    {
        switch ($this->type) {
            case 'phone':
                return array_merge(
                    $this->additional,
                    [
                        'type' => $this->type,
                        'number' => $this->id
                    ]
                );
            default:
                throw new \RuntimeException('unknown type: ' . $this->type);
        }
    }
}
