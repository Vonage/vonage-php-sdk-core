<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Client\Credentials;

class SignatureSecret extends AbstractCredentials
{
    /**
     * Create a credential set with an API key and signature secret.
     *
     * @param $key
     * @param $signature_secret
     * @param string $method
     */
    public function __construct($key, $signature_secret, $method = 'md5hash')
    {
        $this->credentials['api_key'] = $key;
        $this->credentials['signature_secret'] = $signature_secret;
        $this->credentials['signature_method'] = $method;
    }
}
