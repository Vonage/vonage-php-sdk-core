<?php

declare(strict_types=1);

namespace Vonage\Client\Response;

abstract class AbstractResponse implements ResponseInterface
{
    /**
     * @var array
     */
    protected $data;

    public function getData(): array
    {
        return $this->data;
    }

    public function isSuccess(): bool
    {
        return isset($this->data['status']) && (int)$this->data['status'] === 0;
    }

    public function isError(): bool
    {
        return !$this->isSuccess();
    }
}
