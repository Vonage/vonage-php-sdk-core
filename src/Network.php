<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage;

use ArrayAccess;
use JsonSerializable;
use Vonage\Client\Exception\Exception;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;

/**
 * This class will no longer be accessible via array access, nor contain request/response information after v2.
 */
class Network implements
    EntityInterface,
    JsonSerializable,
    JsonSerializableInterface,
    JsonUnserializableInterface,
    ArrayAccess,
    ArrayHydrateInterface
{
    use JsonSerializableTrait;
    use NoRequestResponseTrait;
    use JsonResponseTrait;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Network constructor.
     *
     * @param $networkCode
     * @param $networkName
     */
    public function __construct($networkCode, $networkName)
    {
        $this->data['network_code'] = $networkCode;
        $this->data['network_name'] = $networkName;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->data['network_code'];
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->data['network_name'];
    }

    /**
     * @return mixed
     */
    public function getOutboundSmsPrice()
    {
        return $this->data['sms_price'] ?? $this->data['price'];
    }

    /**
     * @return mixed
     */
    public function getOutboundVoicePrice()
    {
        return $this->data['voice_price'] ?? $this->data['price'];
    }

    /**
     * @return mixed
     */
    public function getPrefixPrice()
    {
        return $this->data['mt_price'];
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->data['currency'];
    }

    /**
     * @param array $json
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
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->toArray();
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
     * @throws Exception
     */
    public function offsetSet($offset, $value): void
    {
        throw new Exception('Network is read only');
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('Network is read only');
    }

    /**
     * @param array $data
     */
    public function fromArray(array $data): void
    {
        // Convert CamelCase to snake_case as that's how we use array access in every other object
        $storage = [];

        foreach ($data as $k => $v) {
            $k = strtolower(ltrim(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $k), '_'));
            $storage[$k] = $v;
        }

        $this->data = $storage;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
