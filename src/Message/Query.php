<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Message;

use DateTime;

class Query
{
    protected $params = [];

    /**
     * Query constructor.
     * @param DateTime $date
     * @param $to
     */
    public function __construct(DateTime $date, $to)
    {
        $this->params['date'] = $date->format('Y-m-d');
        $this->params['to'] = $to;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
