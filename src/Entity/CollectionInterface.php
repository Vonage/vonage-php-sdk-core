<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity;

use Countable;
use Iterator;

interface CollectionInterface extends Countable, Iterator
{

    public static function getCollectionName(): string;

    public static function getCollectionPath(): string;

    /**
     * @param $data
     * @param $idOrEntity
     */
    public function hydrateEntity($data, $idOrEntity);
}
