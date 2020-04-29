<?php
declare(strict_types=1);

namespace Nexmo\Message;

use Nexmo\Client\Exception\Server;

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