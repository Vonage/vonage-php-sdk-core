<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Entity;

/**
 * Store request data in an array, and lock once a request object has been set.
 */
trait RequestArrayTrait
{
    /**
     * @var array
     */
    protected $requestData = [];

    /**
     * Get an array of params to use in an API request.
     */
    public function getRequestData($sent = true)
    {
        if(!($this instanceof EntityInterface)){
            throw new \Exception(sprintf(
                '%s can only be used if the class implements %s',
                __TRAIT__,
                EntityInterface::class
            ));
        }

        if($sent && ($request = $this->getRequest())){
            $query = [];
            parse_str($request->getUri()->getQuery(), $query);
            return $query;
        }

        return $this->requestData;
    }    
    
    protected function setRequestData($name, $value)
    {
        if(!($this instanceof EntityInterface)){
            throw new \Exception(sprintf(
                '%s can only be used if the class implements %s',
                __TRAIT__,
                EntityInterface::class
            ));
        }

        if($this->getResponse()){
            throw new \RuntimeException(sprintf(
                'can not set request parameter `%s` for `%s` after API request has be made',
                $name,
                get_class($this)
            ));
        }

        $this->requestData[$name] = $value;
        return $this;
    }
}