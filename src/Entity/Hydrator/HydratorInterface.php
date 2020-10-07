<?php
/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license   MIT <https://github.com/vonage/vonage-php/blob/master/LICENSE>
 */
declare(strict_types=1);

namespace Vonage\Entity\Hydrator;

interface HydratorInterface
{
    /**
     * Hydrate an object that the hydrator creates
     *
     * @param array $data
     */
    public function hydrate(array $data);

    /**
     * Hydrate an existing object created outside of the hydrator
     *
     * @param array $data
     * @param $object
     */
    public function hydrateObject(array $data, $object);
}
