<?php

declare(strict_types=1);

namespace Vonage\Entity;

use Countable;
use Iterator;

interface CollectionInterface extends Countable, Iterator
{

    public static function getCollectionName(): string;

    public static function getCollectionPath(): string;

    /**
     * @param $data
     * @param $idOrEntity
     */
    public function hydrateEntity($data, $idOrEntity);
}
