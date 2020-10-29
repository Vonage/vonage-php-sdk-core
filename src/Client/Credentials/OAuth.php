<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Credentials;

use function array_combine;
use function func_get_args;

/**
 * @property string token
 * @property string token_secret
 * @property string consumer_key
 * @property string consumer_secret
 */
class OAuth extends AbstractCredentials
{
    /**
     * Create a credential set with OAuth credentials.
     */
    public function __construct($consumerToken, $consumerSecret, $token, $secret)
    {
        //using keys that match guzzle
        $this->credentials = array_combine(
            ['consumer_key', 'consumer_secret', 'token', 'token_secret'],
            func_get_args()
        );
    }
}
