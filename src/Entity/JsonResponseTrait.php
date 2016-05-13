<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Entity;

use Psr\Http\Message\ResponseInterface;

trait JsonResponseTrait
{
    protected $responseJson;

    public function getResponseData()
    {
        if(isset($this->responseJson)){
            return $this->responseJson;
        }

        if(isset($this->response) && ($this->response instanceof ResponseInterface)){
            $body = $this->response->getBody()->getContents();
            $this->responseJson = json_decode($body, true);
            return $this->responseJson;
        }

        return [];
    }
}
