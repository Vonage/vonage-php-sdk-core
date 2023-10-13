<?php

namespace Vonage\Verify2\VerifyObjects;

class VerificationLocale
{
    private array $allowedCodes = [
        'fr-fr',
        'en-gb',
        'en-us',
        'es-es',
        'es-us',
        'it-it',
        'de-de',
        'pt-br',
        'pt-pt',
        'ru-ru',
        'hi-in',
        'id-id',
        'he-il',
        'yue-cn',
        'ja-jp',
        'ar-xa',
        'cs-cz',
        'cy-gb',
        'el-gr',
        'en-au',
        'en-in',
        'es-mx',
        'fi-fi',
        'fil-ph',
        'fr-ca',
        'hu-hu',
        'is-is',
        'nb-no',
        'nl-nl',
        'pl-pl',
        'ro-ro',
        'sv-se',
        'th-th',
        'tr-tr',
        'vi-vn',
        'zh-cn',
        'zh-tw'
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