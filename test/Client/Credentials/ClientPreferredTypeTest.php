<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace VonageTest\Client\Credentials;

use Vonage\Client\APIResource;
use Vonage\Client\Credentials\Keypair;
use Vonage\Client\Credentials\OAuth;
use Vonage\Client\Credentials\SignatureSecret;
use Vonage\Client\Exception\Credentials;
use Vonage\Messages\Client;
use VonageTest\VonageTestCase;
use Vonage\Client\Credentials\Basic;

class ClientPreferredTypeTest extends VonageTestCase
{
    /**
     * @dataProvider credentialTypes
     */
    public function testAllowedCredentialTypes($credentialType, $allowedType): void
    {
        if (!$allowedType) {
            $this->expectException(Credentials::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $client = new Client(new APIResource());
        $client->setPreferredCredentialsClass($credentialType);
    }

    public function credentialTypes(): array
    {
        return [
            [Basic::class, true],
            [Keypair::class, true],
            [OAuth::class, true],
            [SignatureSecret::class, true],
            ['Vonage\Credentials\Baconator', false]
        ];
    }
}
