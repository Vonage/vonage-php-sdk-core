<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Credentials;

abstract class AbstractCredentials implements CredentialsInterface
{
    /**
     * @var array
     */
    protected array $credentials = [];

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
}
