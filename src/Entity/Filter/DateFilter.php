<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity\Filter;

use DateTime;

/**
 * Simple value object for application filtering.
 */
class DateFilter implements FilterInterface
{
    public const FORMAT = 'Y:m:d:H:i:s';

    protected $start;
    protected $end;

    public function __construct(DateTime $start, DateTime $end)
    {
        if ($start < $end) {
            $this->start = $start;
            $this->end = $end;
        } else {
            $this->start = $end;
            $this->end = $start;
        }
    }

    /**
     * @return string[]
     */
    public function getQuery(): array
    {
        return [
            'date' => $this->start->format(self::FORMAT) . '-' . $this->end->format(self::FORMAT)
        ];
    }
}
