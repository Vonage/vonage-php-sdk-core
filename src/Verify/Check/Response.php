<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify\Check;

use Nexmo\Client\Response\Response as BaseResponse;
use Nexmo\Client\Response\ResponseInterface;

class Response extends BaseResponse implements ResponseInterface
{
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