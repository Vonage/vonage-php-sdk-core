<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Credentials;

/**
 * Class Basic
 * Read-only container for api key and secret.
 *
 * @property string api_key
 * @property string api_secret
 */
class Basic extends AbstractCredentials
{
    /**
     * Create a credential set with an API key and secret.
     *
     * @param $key
     * @param $secret
     */
    public function __construct($key, $secret)
    {
        $this->credentials['api_key'] = (string)$key;
        $this->credentials['api_secret'] = (string)$secret;
    }
}
