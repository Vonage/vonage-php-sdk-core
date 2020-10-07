<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Entity\Filter;

class EmptyFilter implements FilterInterface
{
    /**
     * @return array
     */
    public function getQuery(): array
    {
        return [];
    }
}
