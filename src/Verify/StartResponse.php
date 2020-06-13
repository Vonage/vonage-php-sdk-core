<?php
declare(strict_types=1);

namespace Nexmo\Verify;

class StartResponse
{
    /**
     * @var array{status: int, request_id: string}
     */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getStatus() : int
    {
        return (int) $this->data['status'];
    }

    public function getRequestId() : string
    {
        return $this->data['request_id'];
    }
}