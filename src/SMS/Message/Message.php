<?php
declare(strict_types=1);

namespace Nexmo\SMS\Message;

interface Message
{
    public function toArray() : array;
}
