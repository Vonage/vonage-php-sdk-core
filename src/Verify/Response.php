<?php
/**
 * Nexmo Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Nexmo, Inc. (http://nexmo.com)
 * @license   https://github.com/Nexmo/nexmo-php/blob/master/LICENSE.txt MIT License
 */

namespace Nexmo\Verify;

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