<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Entity;

interface CollectionInterface extends \Countable, \Iterator
{
    /**
     * @return string
     */
    public static function getCollectionName();

    /**
     * @return string
     */
    public static function getCollectionPath();

    /**
     * @param $data
     * @param $idOrEntity
     * @return mixed
     */
    public function hydrateEntity($data, $idOrEntity);
}
