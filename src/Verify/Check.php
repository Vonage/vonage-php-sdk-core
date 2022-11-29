<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Verify;

use DateTime;
use Exception;

class Check
{
    /**
     * Possible status of checking a code.
     */
    public const VALID = 'VALID';
    public const INVALID = 'INVALID';

    public function __construct(protected array $data)
    {
    }

    public function getCode()
    {
        return $this->data['code'];
    }

    /**
     * @throws Exception
     */
    public function getDate(): DateTime
    {
        return new DateTime($this->data['date_received']);
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function getIpAddress()
    {
        return $this->data['ip_address'];
    }
}
