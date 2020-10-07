<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

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
    public function hasApi($api): bool;

    /**
     * @param $api
     * @return mixed
     */
    public function getApi($api);
}
