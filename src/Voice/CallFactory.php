<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Voice;

use Exception;
use Vonage\Entity\Factory\FactoryInterface;

class CallFactory implements FactoryInterface
{
    /**
     * @param array $data
     * @return Call
     * @throws Exception
     */
    public function create(array $data): Call
    {
        return new Call($data);
    }
}
