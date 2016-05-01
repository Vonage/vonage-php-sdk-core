<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify\Search;
use Nexmo\Client\Request\AbstractRequest;
use Nexmo\Client\Request\RequestInterface;
use Nexmo\Client\Request\WrapResponseInterface;
use Nexmo\Client\Response\Error;
use Nexmo\Client\Response\ResponseInterface;

class Request extends AbstractRequest implements RequestInterface, WrapResponseInterface
{
    protected $params = array();

    public function __construct($id)
    {
        $this->params['request_id'] = $id;
    }

    /**
     * @return string
     */
    public function getURI()
    {
        return '/verify/search/json';
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