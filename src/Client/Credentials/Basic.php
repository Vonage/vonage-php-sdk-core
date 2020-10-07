<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
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
        $this->credentials['api_key'] = $key;
        $this->credentials['api_secret'] = $secret;
    }
}
