<?php

declare(strict_types=1);

namespace Vonage\Client\Credentials;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Vonage\Application\Application;
use Vonage\JWT\TokenGenerator;

/**
 * @property mixed application
 */
class Keypair extends AbstractCredentials
{
    public function __construct(protected string $key, protected ?string $application = null)
    {
        $this->credentials['key'] = $key;

        if ($application) {
            if ($application instanceof Application) {
                $application = $application->getId();
            }

            $this->credentials['application'] = $application;
        }
    }

    /**
     * @deprecated Old public signature using Lcobucci/Jwt directly
     */
    public function getKey(): Key
    {
        return InMemory::plainText($this->key);
    }

    public function getKeyRaw(): string
    {
        return $this->key;
    }

    public function getApplication(): ?string
    {
        return $this->application;
    }

    public function setApplication(mixed $application): Keypair
    {
        $this->application = $application;
        return $this;
    }

    public function generateJwt(array $claims = []): Token
    {
        $generator = new TokenGenerator($this->application, $this->getKeyRaw());

        if (isset($claims['exp'])) {
            // This will change to an Exception in 5.0
            trigger_error('Expiry date is automatically generated from now and TTL, so cannot be passed in
            as an argument in claims', E_USER_WARNING);
            unset($claims['nbf']);
        }

        if (isset($claims['ttl'])) {
            $generator->setTTL($claims['ttl']);
            unset($claims['ttl']);
        }

        if (isset($claims['jti'])) {
            $generator->setJTI($claims['jti']);
            unset($claims['jti']);
        }

        if (isset($claims['nbf'])) {
            // Due to older versions of lcobucci/jwt, this claim has
            // historic fraction conversation issues. For now, nbf is not supported.
            // This will change to an Exception in 5.0
            trigger_error('NotBefore Claim is not supported in Vonage JWT', E_USER_WARNING);
            unset($claims['nbf']);
        }

        if (isset($claims['sub'])) {
            $generator->setSubject($claims['sub']);
            unset($claims['sub']);
        }

        if (!empty($claims)) {
            foreach ($claims as $claim => $value) {
                $generator->addClaim($claim, $value);
            }
        }

        $jwt = $generator->generate();
        $parser = new Token\Parser(new JoseEncoder());

        // Backwards compatible for signature. In 5.0 this will return a string value
        return $parser->parse($jwt);
    }
}
