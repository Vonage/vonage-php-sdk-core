<?php
declare(strict_types=1);

namespace Vonage\Voice;

use Vonage\Entity\Factory\FactoryInterface;

class CallFactory implements FactoryInterface
{
    /**
     * @return Call
     */
    public function create(array $data)
    {
        return new Call($data);
    }
}
