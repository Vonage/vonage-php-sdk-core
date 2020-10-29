<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Message;

trait CollectionTrait
{
    /**
     * @var int
     */
    protected $index;

    /**
     * @param $index
     */
    public function setIndex($index): void
    {
        $this->index = (int)$index;
    }
}
