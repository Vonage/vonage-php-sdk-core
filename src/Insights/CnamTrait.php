<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Insights;

trait CnamTrait
{
    /**
     * @return mixed
     */
    public function getCallerName()
    {
        return $this->data['caller_name'];
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->data['first_name'];
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->data['last_name'];
    }

    /**
     * @return mixed
     */
    public function getCallerType()
    {
        return $this->data['caller_type'];
    }
}
