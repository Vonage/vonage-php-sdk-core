<?php
/**
 * Created by PhpStorm.
 * User: tjlytle
 * Date: 10/28/14
 * Time: 10:56 PM
 */

namespace Nexmo\Verify\Check;
use Nexmo\Client\Request\AbstractRequest;
use Nexmo\Client\Request\RequestInterface;
use Nexmo\Client\Request\WrapResponseInterface;
use Nexmo\Client\Response\Error;
use Nexmo\Client\Response\ResponseInterface;

class Request extends AbstractRequest implements RequestInterface, WrapResponseInterface
{
    protected $params = array();

    public function __construct($id, $code, $ip = null)
    {
        $this->params['request_id'] = $id;
        $this->params['code'] = $code;
        $this->params['ip_address'] = $ip;
    }

    /**
     * @return string
     */
    public function getURI()
    {
        return '/verify/check/json';
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