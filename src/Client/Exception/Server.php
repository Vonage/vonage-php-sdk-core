<?php

declare(strict_types=1);

namespace Vonage\Client\Exception;

use Vonage\Entity\HasEntityTrait;
use Vonage\Entity\Psr7Trait;

class Server extends Exception
{
    use HasEntityTrait;
    use Psr7Trait;
}
