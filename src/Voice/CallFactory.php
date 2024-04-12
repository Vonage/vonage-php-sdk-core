<?php

declare(strict_types=1);

namespace Vonage\Voice;

use Exception;
use Vonage\Entity\Factory\FactoryInterface;

class CallFactory implements FactoryInterface
{
    /**
     * @throws Exception
     */
    public function create(array $data): Call
    {
        return new Call($data);
    }
}
