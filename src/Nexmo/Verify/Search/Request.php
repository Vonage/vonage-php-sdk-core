<?php
/**
 * Created by PhpStorm.
 * User: tjlytle
 * Date: 10/28/14
 * Time: 10:57 PM
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