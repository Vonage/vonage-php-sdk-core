<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2019 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Entity;

/**
 * Common code for iterating over a collection, and using the collection class to discover the API path.
 */
trait ModernCollectionTrait
{
    use CollectionTrait;
    
    /**
     * Count of total items
     * @return integer
     */
    public function count()
    {
        if (isset($this->page)) {
            return (int) $this->page['total_items'];
        }
    }

    public function getPage()
    {
        if (isset($this->page)) {
            return $this->page['page'];
        }

        if (isset($this->index)) {
            return $this->index;
        }

        throw new \RuntimeException('page not set');
    }
}
