<?php
declare(strict_types=1);

namespace Vonage\Client\Exception;

class ThrottleException extends Server
{
    /**
     * @var int
     */
    protected $timeout;

    public function setTimeout(int $seconds) : void
    {
        $this->timeout = $seconds;
    }

    public function getTimeout() : int
    {
        return $this->timeout;
    }
}
