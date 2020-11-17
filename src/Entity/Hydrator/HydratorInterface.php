<?php

/**
 * Vonage Client Library for PHP
 *
 * @copyright Copyright (c) 2016-2020 Vonage, Inc. (http://vonage.com)
 * @license https://github.com/Vonage/vonage-php-sdk-core/blob/master/LICENSE.txt Apache License 2.0
 */

declare(strict_types=1);

namespace Vonage\Entity\Hydrator;

interface HydratorInterface
{
    /**
     * Hydrate an object that the hydrator creates
     */
    public function hydrate(array $data);

    /**
     * Hydrate an existing object created outside of the hydrator
     *
     * @param $object
     */
    public function hydrateObject(array $data, $object);
}
