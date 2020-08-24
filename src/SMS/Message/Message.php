<?php
declare(strict_types=1);

namespace Vonage\SMS\Message;

interface Message
{
    public function toArray() : array;
}
