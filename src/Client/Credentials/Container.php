<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Client\Credentials;

use RuntimeException;

class Container extends AbstractCredentials
{
    protected $types = [
        Basic::class,
        SignatureSecret::class,
        Keypair::class
    ];

    protected $credentials;

    /**
     * Container constructor.
     *
     * @param $credentials
     */
    public function __construct($credentials)
    {
        if (!is_array($credentials)) {
            $credentials = func_get_args();
        }

        foreach ($credentials as $credential) {
            $this->addCredential($credential);
        }
    }

    /**
     * @param CredentialsInterface $credential
     */
    protected function addCredential(CredentialsInterface $credential): void
    {
        $type = $this->getType($credential);
        if (isset($this->credentials[$type])) {
            throw new RuntimeException('can not use more than one of a single credential type');
        }

        $this->credentials[$type] = $credential;
    }

    /**
     * @param CredentialsInterface $credential
     * @return string|null
     */
    protected function getType(CredentialsInterface $credential): ?string
    {
        foreach ($this->types as $type) {
            if ($credential instanceof $type) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function get($type)
    {
        if (!isset($this->credentials[$type])) {
            throw new RuntimeException('credential not set');
        }

        return $this->credentials[$type];
    }

    /**
     * @param $type
     * @return bool
     */
    public function has($type): bool
    {
        return isset($this->credentials[$type]);
    }

    /**
     * @param $claims
     * @return mixed
     */
    public function generateJwt($claims)
    {
        return $this->credentials[Keypair::class]->generateJwt($claims);
    }
}
