<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Client\Credentials;

class OAuth extends AbstractCredentials implements CredentialsInterface
{
    /**
     * Create a credential set with OAuth credentials.
    */
    public function __construct(string $consumerToken, string $consumerSecret, string $token, string $secret)
    {
        //using keys that match guzzle
        $this->credentials = [
            'consumer_key' => $consumerToken,
            'consumer_secret' => $consumerSecret,
            'token' => $token,
            'token_secret' => $secret
        ];
    }
}
