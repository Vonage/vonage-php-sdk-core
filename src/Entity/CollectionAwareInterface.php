<?php

declare(strict_types=1);

namespace Vonage\Entity;

interface CollectionAwareInterface
{

    public function setCollection(CollectionInterface $collection);

    public function getCollection(): CollectionInterface;
}
