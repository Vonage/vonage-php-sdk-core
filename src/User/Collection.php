<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2018 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\User;

use Nexmo\Entity\Collection as EntityCollection;

class Collection extends EntityCollection
{
    public function count()
    {
        if (isset($this->page)) {
            return count($this->page);
        }
    }
}
