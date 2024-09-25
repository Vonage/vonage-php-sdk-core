<?php

declare(strict_types=1);

namespace Vonage\Entity\Filter;

use DateTime;

/**
 * Simple value object for application filtering.
 */
class DateFilter implements FilterInterface
{
    public const FORMAT = 'Y:m:d:H:i:s';

    protected DateTime $start;
    protected DateTime $end;

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

    public function getQuery(): array
    {
        return [
            'date' => $this->start->format(self::FORMAT) . '-' . $this->end->format(self::FORMAT)
        ];
    }
}
