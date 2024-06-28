<?php

declare(strict_types=1);

namespace Vonage\Client\Credentials;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token;
use Vonage\JWT\TokenGenerator;

class Gnp extends AbstractCredentials
{
    protected ?string $code = null;
    protected ?string $state = null;
    protected ?string $redirectUri = null;

    public function __construct(
        protected string $msisdn,
        protected string $key,
        protected $application = null
    ) {
    }

    public function getKeyRaw(): string
    {
        return $this->key;
    }

    public function getMsisdn(): string
    {
        return $this->msisdn;
    }

    public function setMsisdn(string $msisdn): Gnp
    {
        $this->msisdn = $msisdn;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): Gnp
    {
        $this->code = $code;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): Gnp
    {
        $this->state = $state;
        return $this;
    }

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(?string $redirectUri): Gnp
    {
        $this->redirectUri = $redirectUri;
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

    public function getApplication(): ?string
    {
        return $this->application;
    }

    public function setApplication(mixed $application): Keypair
    {
        $this->application = $application;
        return $this;
    }
}
