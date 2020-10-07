<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Entity;

use Countable;
use Iterator;

interface CollectionInterface extends Countable, Iterator
{
    /**
     * @return string
     */
    public static function getCollectionName(): string;

    /**
     * @return string
     */
    public static function getCollectionPath(): string;

    /**
     * @param $data
     * @param $idOrEntity
     * @return mixed
     */
    public function hydrateEntity($data, $idOrEntity);
}
