<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */
declare(strict_types=1);

namespace Vonage\Account;

use ArrayAccess;
use JsonSerializable;
use Vonage\Client\Exception\Exception;
use Vonage\Entity\Hydrator\ArrayHydrateInterface;
use Vonage\Entity\JsonSerializableInterface;
use Vonage\Entity\JsonUnserializableInterface;

/**
 * This class will no longer be accessible via array keys past v2
 * @todo Have the JSON unserialize/serialize keys match with $this->data keys
 */
class Balance implements
    JsonSerializable,
    JsonSerializableInterface,
    JsonUnserializableInterface,
    ArrayAccess,
    ArrayHydrateInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param $balance
     * @param $autoReload
     * @todo Have these take null values, since we offer an unserialize option to populate
     */
    public function __construct($balance, $autoReload)
    {
        $this->data['balance'] = $balance;
        $this->data['auto_reload'] = $autoReload;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->data['balance'];
    }

    /**
     * @return mixed
     */
    public function getAutoReload()
    {
        return $this->data['auto_reload'];
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
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->data;
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
        throw new Exception('Balance is read only');
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('Balance is read only');
    }

    /**
     * @param array $data
     */
    public function fromArray(array $data): void
    {
        $this->data = [
            'balance' => $data['value'],
            'auto_reload' => $data['autoReload']
        ];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param $key
     * @return array|null
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($key): ?array
    {
        if ($key === 'data') {
            trigger_error(
                "Direct access to " . get_class($this) . "::data is deprecated, please use getter to toArray() methods",
                E_USER_DEPRECATED
            );

            return $this->data;
        }

        return null;
    }
}
