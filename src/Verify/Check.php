<?php

declare(strict_types=1);

namespace Vonage\Verify;

use DateTime;
use Exception;

class Check
{
    /**
     * Possible status of checking a code.
     *
     * @deprecated Use Vonage\Verify\CheckAttempt::VALID and CheckAttempt::INVALID instead.
     */
    public const VALID = 'VALID';

    /**
     * @deprecated Use Vonage\Verify\CheckAttempt::INVALID instead.
     */
    public const INVALID = 'INVALID';

    public function __construct(protected array $data)
    {
    }

    /**
     * @deprecated Use Vonage\Verify\CheckAttempt instead.
     */
    public function getCode(): mixed
    {
        trigger_error(
            'Vonage\\Verify\\Check::getCode() is deprecated. Use Vonage\\Verify\\CheckAttempt instead.',
            E_USER_DEPRECATED
        );
        return $this->data['code'];
    }

    /**
     * @throws Exception
     *
     * @deprecated Use Vonage\Verify\CheckAttempt instead.
     */
    public function getDate(): DateTime
    {
        trigger_error(
            'Vonage\\Verify\\Check::getDate() is deprecated. Use Vonage\\Verify\\CheckAttempt instead.',
            E_USER_DEPRECATED
        );
        return new DateTime($this->data['date_received']);
    }

    /**
     * @deprecated Use Vonage\Verify\CheckAttempt instead.
     */
    public function getStatus()
    {
        trigger_error(
            'Vonage\\Verify\\Check::getStatus() is deprecated. Use Vonage\\Verify\\CheckAttempt instead.',
            E_USER_DEPRECATED
        );
        return $this->data['status'];
    }

    /**
     * @deprecated Use Vonage\Verify\CheckAttempt instead.
     */
    public function getIpAddress()
    {
        trigger_error(
            'Vonage\\Verify\\Check::getIpAddress() is deprecated. Use Vonage\\Verify\\CheckAttempt instead.',
            E_USER_DEPRECATED
        );
        return $this->data['ip_address'];
    }
}
