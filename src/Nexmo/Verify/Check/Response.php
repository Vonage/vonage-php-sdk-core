<?php
/**
 * Created by PhpStorm.
 * User: tjlytle
 * Date: 10/28/14
 * Time: 10:56 PM
 */

namespace Nexmo\Verify\Check;

use Nexmo\Client\Response\Response as BaseResponse;
use Nexmo\Client\Response\ResponseInterface;

class Response extends BaseResponse implements ResponseInterface
{
    protected $expected = array('event_id', 'status', 'price', 'currency');

    public function getEventId()
    {
        return $this->data['event_id'];
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function getPrice()
    {
        return $this->data['price'];
    }

    public function getCurrency()
    {
        return $this->data['currency'];
    }

}