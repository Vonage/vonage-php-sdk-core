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
use Vonage\Client\Exception\Exception;
use Vonage\InvalidResponseException;

class Secret implements ArrayAccess
{
    protected $data;

    /**
     * Secret constructor.
     *
     * @param $data
     * @throws InvalidResponseException
     */
    public function __construct($data)
    {
        if (!isset($data['id'])) {
            throw new InvalidResponseException("Missing key: 'id");
        }
        if (!isset($data['created_at'])) {
            throw new InvalidResponseException("Missing key: 'created_at");
        }

        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->data['id'];
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->data['created_at'];
    }

    /**
     * @return mixed
     */
    public function getLinks()
    {
        return $this->data['_links'];
    }

    /**
     * @param $data
     * @return Secret
     * @throws InvalidResponseException
     * @deprecated Instantiate the object directly
     */
    public static function fromApi($data): Secret
    {
        trigger_error('Please instantiate a Vonage\Account\Secret object instead of using fromApi', E_USER_DEPRECATED);

        return new self($data);
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
        throw new Exception('Secret::offsetSet is not implemented');
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('Secret::offsetUnset is not implemented');
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
