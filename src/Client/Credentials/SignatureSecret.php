<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Credentials;

class SignatureSecret extends AbstractCredentials
{
    /**
     * Create a credential set with an API key and signature secret.
     */
    public function __construct($key, $signature_secret, string $method = 'md5hash')
    {
        $this->credentials['api_key'] = $key;
        $this->credentials['signature_secret'] = $signature_secret;
        $this->credentials['signature_method'] = $method;
    }
}
