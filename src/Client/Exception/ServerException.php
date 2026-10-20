<?php

declare(strict_types=1);

namespace Vonage\Client\Exception;

use Vonage\Entity\HasEntityTrait;

class ServerException extends Exception
{
    use HasEntityTrait;
}
