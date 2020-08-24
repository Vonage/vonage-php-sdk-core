<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Entity\Filter;

/**
 * Simple value object for application filtering.
 */
class DateFilter implements FilterInterface
{
    const FORMAT = 'Y:m:d:H:i:s';

    protected $start;
    protected $end;

    public function __construct(\DateTime $start, \DateTime $end)
    {
        if ($start < $end) {
            $this->start = $start;
            $this->end = $end;
        } else {
            $this->start = $end;
            $this->end = $start;
        }
    }

    public function getQuery()
    {
        return [
            'date' => $this->start->format(self::FORMAT) . '-' . $this->end->format(self::FORMAT)
        ];
    }
}
