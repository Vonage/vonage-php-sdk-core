<?php

namespace Vonage\Verify2\VerifyObjects;

class VerificationLocale
{
    private array $allowedCodes = [
        'en-us',
        'en-gb',
        'es-es',
        'es-mx',
        'es-us',
        'it-it',
        'fr-fr',
        'de-de',
        'ru-ru',
        'hi-in',
        'pt-br',
        'pt-pt',
        'id-id',
    ];

    public function __construct(protected string $code = 'en-us')
    {
        if (! in_array($code, $this->allowedCodes, true)) {
            throw new \InvalidArgumentException('Invalid Locale Code Provided');
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }
}