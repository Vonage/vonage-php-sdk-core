<?php

declare(strict_types=1);

namespace VonageTest\Verify2\VerifyObjects;

use PHPUnit\Framework\TestCase;
use Vonage\Verify2\VerifyObjects\VerificationLocale;

class VerificationLocaleTest extends TestCase
{
    public function testDefaultCodeIsSet()
    {
        $locale = new VerificationLocale();
        $this->assertSame('en-us', $locale->getCode());
    }

    public function testGetCodeReturnsCorrectValue()
    {
        $locale = new VerificationLocale('fr-fr');
        $this->assertSame('fr-fr', $locale->getCode());
    }

    public function testSetCodeUpdatesCode()
    {
        $locale = new VerificationLocale();
        $locale->setCode('es-es');

        $this->assertSame('es-es', $locale->getCode());
    }

    public function testSetCodeReturnsSelfForChaining()
    {
        $locale = new VerificationLocale();
        $result = $locale->setCode('it-it');

        $this->assertInstanceOf(VerificationLocale::class, $result);
    }
}