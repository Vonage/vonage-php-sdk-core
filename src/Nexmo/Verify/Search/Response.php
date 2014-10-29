<?php
/**
 * Created by PhpStorm.
 * User: tjlytle
 * Date: 10/28/14
 * Time: 10:57 PM
 */

namespace Nexmo\Verify\Search;

use Nexmo\Client\Response\Response as BaseResponse;
use Nexmo\Client\Response\ResponseInterface;

class Response extends BaseResponse implements ResponseInterface
{
    protected $expected = array('request_id', 'status');

    public function getId()
    {
        return $this->data['request_id'];
    }

    public function getStatus()
    {
        return $this->data['status'];
    }
}