<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage;

use ArrayAccess;
use JsonSerializable;
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\Entity\EntityInterface;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonResponseTrait;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonSerializableTrait;
use Vonage\Entity\JsonUnserializableInterface;
use Vonage\Entity\NoRequestResponseTrait;

use function get_class;
use function ltrim;
use function preg_replace;
use function strtolower;
use function trigger_error;

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
     * @param string|int $networkCode
     * @param string|int $networkName
     */
    public function __construct($networkCode, $networkName)
    {
        $this->data['network_code'] = (string)$networkCode;
        $this->data['network_name'] = (string)$networkName;
    }

    public function getCode(): string
    {
        return $this->data['network_code'];
    }

    public function getName(): string
    {
        return $this->data['network_name'];
    }

    public function getOutboundSmsPrice()
    {
        return $this->data['sms_price'] ?? $this->data['price'];
    }

    public function getOutboundVoicePrice()
    {
        return $this->data['voice_price'] ?? $this->data['price'];
    }

    public function getPrefixPrice()
    {
        return $this->data['mt_price'];
    }

    public function getCurrency()
    {
        return $this->data['currency'];
    }

    public function jsonUnserialize(array $json): void
    {
        trigger_error(
            get_class($this) . "::jsonUnserialize is deprecated, please fromArray() instead",
            E_USER_DEPRECATED
        );

        $this->fromArray($json);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function offsetExists($offset): bool
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

    /**
     * @throws ClientException
     */
    public function offsetSet($offset, $value): void
    {
        throw new ClientException('Network is read only');
    }

    /**
     * @throws ClientException
     */
    public function offsetUnset($offset): void
    {
        throw new ClientException('Network is read only');
    }

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
