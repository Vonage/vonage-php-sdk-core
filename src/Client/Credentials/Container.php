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

use function func_get_args;
use function is_array;

class Container extends AbstractCredentials
{
    protected $types = [
        Basic::class,
        SignatureSecret::class,
        Keypair::class
    ];

    /**
     * @var array
     */
    protected array $credentials;

    public function __construct($credentials)
    {
        if (!is_array($credentials)) {
            $credentials = func_get_args();
        }

        foreach ($credentials as $credential) {
            $this->addCredential($credential);
        }
    }

    protected function addCredential(CredentialsInterface $credential): void
    {
        $type = $this->getType($credential);

        if (isset($this->credentials[$type])) {
            throw new RuntimeException('can not use more than one of a single credential type');
        }

        $this->credentials[$type] = $credential;
    }

    protected function getType(CredentialsInterface $credential): ?string
    {
        foreach ($this->types as $type) {
            if ($credential instanceof $type) {
                return $type;
            }
        }

        return null;
    }

    public function get($type)
    {
        if (!isset($this->credentials[$type])) {
            throw new RuntimeException('credential not set');
        }

        return $this->credentials[$type];
    }

    public function has($type): bool
    {
        return isset($this->credentials[$type]);
    }

    public function generateJwt($claims)
    {
        return $this->credentials[Keypair::class]->generateJwt($claims);
    }
}
