<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2019 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Entity;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;
use Vonage\Application\Application;

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
