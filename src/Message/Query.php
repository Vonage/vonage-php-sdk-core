<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Message;

use DateTime;

class Query
{
    /**
     * @var array
     */
    protected $params = [];

    public function __construct(DateTime $date, string $to)
    {
        $this->params['date'] = $date->format('Y-m-d');
        $this->params['to'] = $to;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
