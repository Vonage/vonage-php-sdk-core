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
        if($sent && isset($this->request)){
            $query = [];
            parse_str($this->request->getUri()->getQuery(), $query);
            return $query;
        }

        return $this->requestData;
    }    
    
    protected function setRequestData($name, $value)
    {
        if(isset($this->response)){
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