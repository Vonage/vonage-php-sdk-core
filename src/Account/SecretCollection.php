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
use Vonage\Client\Exception\Exception as ClientException;
use Vonage\InvalidResponseException;

use function get_class;
use function trigger_error;

class SecretCollection implements ArrayAccess
{
    protected $data;

    /**
     * @throws InvalidResponseException
     */
    public function __construct(array $secrets, array $links)
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

    public function getSecrets(): array
    {
        return $this->data['secrets'];
    }

    public function getLinks(): array
    {
        return $this->data['_links'];
    }

    /**
     * @throws InvalidResponseException
     *
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
        throw new ClientException('SecretCollection::offsetSet is not implemented');
    }

    /**
     * @throws ClientException
     */
    public function offsetUnset($offset): void
    {
        throw new ClientException('SecretCollection::offsetUnset is not implemented');
    }
}
