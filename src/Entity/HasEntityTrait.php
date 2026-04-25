<?php

declare(strict_types=1);

namespace Vonage\Entity;

/**
 * @deprecated This trait will be removed in the next major version.
 */
trait HasEntityTrait
{
    protected $entity;

    public function setEntity($entity): void
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
