<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Client\Exception;

class Exception extends \Exception
{
    protected $entity;

    /**
     * Sets the entity that generated the exception
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * Returns the entity that generated the exception
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
