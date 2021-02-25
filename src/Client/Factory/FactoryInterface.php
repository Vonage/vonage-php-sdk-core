<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
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
    public function hasApi(string $api): bool;

    public function getApi(string $api);

    public function make(string $key);
}
