<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify;
use Nexmo\Client\Request\AbstractRequest;
use Nexmo\Client\Request\RequestInterface;
use Nexmo\Client\Request\WrapResponseInterface;
use Nexmo\Client\Response\Error;
use Nexmo\Client\Response\ResponseInterface;

class Request extends AbstractRequest implements RequestInterface, WrapResponseInterface
{
    protected $params = array();

    public function __construct($number, $brand, $from = null, $length = null, $lang = null)
    {
        $this->params['number'] = $number;
        $this->params['brand']  = $brand;
        $this->params['sender_id'] = $from;
        $this->params['code_length'] = $length;
        $this->params['lg'] = $lang;

        if(!is_null($length) AND !(4 == $length OR 6 == $length)){
            throw new \InvalidArgumentException('length must be 4 or 6');
        }
    }

    /**
     * @return string
     */
    public function getURI()
    {
        return 'verify/json';
    }

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function wrapResponse(ResponseInterface $response)
    {
        if($response->isError()){
            return new Error($response->getData());
        }

        return new Response($response->getData());
    }

}