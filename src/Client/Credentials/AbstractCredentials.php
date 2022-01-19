<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Credentials;

use RuntimeException;

use function get_class;
use function sprintf;

abstract class AbstractCredentials implements CredentialsInterface
{
    /**
     * @var array
     */
    protected $credentials = [];

    public function offsetExists($offset): bool
    {
        return isset($this->credentials[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->credentials[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw $this->readOnlyException();
    }

    public function offsetUnset($offset): void
    {
        throw $this->readOnlyException();
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
    {
        return $this->credentials[$name];
    }

    public function asArray(): array
    {
        return $this->credentials;
    }

    protected function readOnlyException(): RuntimeException
    {
        return new RuntimeException(
            sprintf(
                '%s is read only, cannot modify using array access.',
                get_class($this)
            )
        );
    }
}
