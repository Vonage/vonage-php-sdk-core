<?php
declare(strict_types=1);

namespace Nexmo\Verify;

class ControlResponse
{
    /**
     * @var array{status: int, command: string}
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

    public function getCommand() : string
    {
        return $this->data['command'];
    }
}