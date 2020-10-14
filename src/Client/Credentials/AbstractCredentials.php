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

abstract class AbstractCredentials implements CredentialsInterface
{
    protected $credentials = [];

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->credentials[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->credentials[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw $this->readOnlyException();
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw $this->readOnlyException();
    }

    /**
     * @param $name
     * @return mixed
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($name)
    {
        return $this->credentials[$name];
    }

    /**
     * @return array
     */
    public function asArray(): array
    {
        return $this->credentials;
    }

    /**
     * @return RuntimeException
     */
    protected function readOnlyException(): RuntimeException
    {
        return new RuntimeException(sprintf(
            '%s is read only, cannot modify using array access.',
            get_class($this)
        ));
    }
}
