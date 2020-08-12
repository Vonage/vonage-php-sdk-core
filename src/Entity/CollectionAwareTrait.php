<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Entity;

trait CollectionAwareTrait
{
    /**
     * @var CollectionInterface
     */
    protected $collection;

    public function setCollection(CollectionInterface $collection)
    {
        $this->collection = $collection;
    }

    public function getCollection()
    {
        if (!isset($this->collection)) {
            throw new \RuntimeException('missing collection');
        }

        return $this->collection;
    }
}
