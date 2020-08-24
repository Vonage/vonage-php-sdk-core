<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016 Vonage, Inc. (http://vonage.com)
 * @license   https://github.com/vonage/vonage-php/blob/master/LICENSE MIT License
 */

namespace Vonage\Verify;

class Check
{
    /**
     * Possible status of checking a code.
     */
    const VALID = 'VALID';
    const INVALID = 'INVALID';

    /**
     * @var array
     */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getCode()
    {
        return $this->data['code'];
    }

    public function getDate()
    {
        return new \DateTime($this->data['date_received']);
    }

    public function getStatus()
    {
        return $this->data['status'];
    }

    public function getIpAddress()
    {
        return $this->data['ip_address'];
    }
}
