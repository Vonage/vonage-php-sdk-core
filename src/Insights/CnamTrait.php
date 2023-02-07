<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2022 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Insights;

trait CnamTrait
{

    public function getCallerName(): ?string
    {
        return $this->data['caller_name'] ?? null;
    }

    public function getFirstName(): ?string
    {
        return $this->data['first_name'] ?? null;
    }

    public function getLastName(): ?string
    {
        return $this->data['last_name'] ?? null;
    }

    public function getCallerType(): ?string
    {
        return $this->data['caller_type'] ?? null;
    }
}
