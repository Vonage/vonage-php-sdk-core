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

class SecretCollection implements ArrayAccess
{
    protected $data;

    /**
     * SecretCollection constructor.
     *
     * @param $secrets
     * @param $links
     * @throws InvalidResponseException
     */
    public function __construct($secrets, $links)
    {
        $this->data = [
            'secrets' => $secrets,
            '_links' => $links
        ];

        foreach ($this->data['secrets'] as $key => $secret) {
            if (!$secret instanceof Secret) {
                $this->data['secrets'][$key] = new Secret($secret);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getSecrets()
    {
        return $this->data['secrets'];
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
     * @return SecretCollection
     * @throws InvalidResponseException
     * @deprecated Instantiate the object directly
     */
    public static function fromApi($data): SecretCollection
    {
        trigger_error(
            'Please instantiate a Vonage\Account\SecretCollection instead of using fromApi()',
            E_USER_DEPRECATED
        );

        $secrets = [];

        foreach ($data['_embedded']['secrets'] as $s) {
            $secrets[] = Secret::fromApi($s);
        }

        return new self($secrets, $data['_links']);
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
        throw new Exception('SecretCollection::offsetSet is not implemented');
    }

    /**
     * @param mixed $offset
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('SecretCollection::offsetUnset is not implemented');
    }
}
