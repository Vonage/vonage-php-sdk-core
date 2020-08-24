<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Client\Factory;

/**
 * Interface FactoryInterface
 *
 * Factor create API clients (clients specific to single API, that leverages Vonage\Client for HTTP communication and
 * common functionality).
 */
interface FactoryInterface
{
    /**
     * @param $api
     * @return bool
     */
    public function hasApi($api);

    /**
     * @param $api
     * @return mixed
     */
    public function getApi($api);
}
