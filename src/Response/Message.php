<?php

declare(strict_types=1);

namespace Vonage\Response;

use RuntimeException;

class Message
{
    public function __construct(protected array $data)
    {
    }

    public function getStatus()
    {
        return $this->checkData('status');
    }

    public function getId()
    {
        return $this->checkData('message-id');
    }

    public function getTo()
    {
        return $this->checkData('to');
    }

    public function getBalance()
    {
        return $this->checkData('remaining-balance');
    }

    public function getPrice()
    {
        return $this->checkData('message-price');
    }

    public function getNetwork()
    {
        return $this->checkData('network');
    }

    public function getErrorMessage(): string
    {
        if (!isset($this->data['error-text'])) {
            return '';
        }

        return $this->checkData('error-text');
    }

    /**
     * @param $param
     */
    protected function checkData($param)
    {
        if (!isset($this->data[$param])) {
            throw new RuntimeException('tried to access ' . $param . ' but data is missing');
        }

        return $this->data[$param];
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
