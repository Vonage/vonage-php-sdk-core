<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Response;

use RuntimeException;

class Message
{
    protected $data;

    /**
     * Message constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->checkData('status');
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->checkData('message-id');
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->checkData('to');
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->checkData('remaining-balance');
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->checkData('message-price');
    }

    /**
     * @return mixed
     */
    public function getNetwork()
    {
        return $this->checkData('network');
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        if (!isset($this->data['error-text'])) {
            return '';
        }

        return $this->checkData('error-text');
    }

    /**
     * @param $param
     * @return mixed
     */
    protected function checkData($param)
    {
        if (!isset($this->data[$param])) {
            throw new RuntimeException('tried to access ' . $param . ' but data is missing');
        }

        return $this->data[$param];
    }

    /**
     * @return mixed
     */
    public function toArray()
    {
        return $this->data;
    }
}
