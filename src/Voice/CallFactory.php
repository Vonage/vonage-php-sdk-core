<?php
declare(strict_types=1);

namespace Nexmo\Voice;

use Nexmo\Entity\Factory\FactoryInterface;

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
