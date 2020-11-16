<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Client\Exception;

class ThrottleException extends Server
{
    /**
     * @var int
     */
    protected $timeout;

    public function setTimeout(int $seconds): void
    {
        $this->timeout = $seconds;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
