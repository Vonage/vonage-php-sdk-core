<?php

declare(strict_types=1);

namespace Vonage\Voice\Call;

/**
 * @deprecated This class will be removed in the next major version.
 */
class Inbound
{
    public function __construct()
    {
        trigger_error(
            'Vonage\\Voice\\Call\\Inbound is deprecated and will be removed in the next major version.',
            E_USER_DEPRECATED
        );
    }
}
