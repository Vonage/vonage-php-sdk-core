<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Numbers;

use Vonage\Entity\Hydrator\HydratorInterface;

class Hydrator implements HydratorInterface
{
    public function hydrate(array $data)
    {
        $number = new Number();
        return $this->hydrateObject($data, $number);
    }

    public function hydrateObject(array $data, $object)
    {
        $object->fromArray($data);
        return $object;
    }
}
